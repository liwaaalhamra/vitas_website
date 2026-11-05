<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Areeba\AreebaException;
use App\Services\Areeba\AreebaPaymentService;
use App\Services\VitasCoreClient;
use Illuminate\Http\Request;

/**
 * Mobile-app entry points for Areeba loan-installment payments. Mirrors the
 * auth + core-lookup flow of VitasController@zain_cash. The gateway return and
 * webhook are shared with the website (PaymentReturnController).
 */
class AreebaPaymentController extends Controller
{
    public function __construct(
        private AreebaPaymentService $areeba,
        private VitasCoreClient $core
    ) {
        // Intentionally not calling parent::__construct(): the base constructor
        // only does admin-section bootstrapping (same as VitasController).
    }

    /**
     * POST /api/areeba/loan
     * Header: token: <access token>
     * -> { redirectUrl, reference }
     */
    public function initLoan(Request $request)
    {
        $accessToken = parent::valid_access_token($request);
        if (!$accessToken) {
            return parent::return_error('Missing Access Token', 102, 'messages.authentication_missing');
        }

        $user = parent::get_user_by_access_token($accessToken);
        if (!$user) {
            return parent::return_error('Invalid Access Token', 401, 'messages.invalid_access_token');
        }

        // 1. Resolve the active loan for this client from the Vitas core.
        $response = parent::middleware_connect([
            'function' => 'GetActiveLoan',
            'params'   => [
                'ClientID'    => $user->employee_id,
                'AppUsername' => env('APP_USERNAME'),
                'AppPassword' => env('APP_PASSWORD'),
            ],
        ]);

        if ($response == false) {
            return parent::return_error('Failed To Connect', 456, 'Failed To Connect');
        }
        if (($response['StatusCode'] ?? null) != 0) {
            if (($response['StatusCode'] ?? null) == 9) {
                return response()->json(null);
            }

            return parent::return_error($response['ActionStatus'] ?? 'Error', 457, $response['ActionStatus'] ?? 'Error');
        }

        $loanNumber = $response['ActiveLoan']['LoanNumber'] ?? null;
        if (!$loanNumber) {
            return parent::return_error('No active loan', 458, 'No active loan');
        }

        // 2. Read the outstanding installment.
        $inquiry = $this->core->inquiry((string) $loanNumber);

        if (!$inquiry['found']) {
            return parent::return_error('No payable installment for this loan', 459, 'No payable installment for this loan');
        }

        // 3. Open the Areeba checkout session. Areeba sends the customer back to
        //    /payment/return, which renders payment.app-result for the in-app browser.
        try {
            $payment = $this->areeba->startCheckout($inquiry['data'], 'app', (int) $user->id, app()->getLocale() === 'en' ? 'en' : 'ar');
        } catch (AreebaException $e) {
            return parent::return_error($e->getMessage(), 460, 'messages.payment_init_failed');
        }

        return response()->json([
            'redirectUrl' => $payment->redirect_url,
            'reference'   => $payment->reference,
            'amount'      => $payment->amount,
            'currency'    => $payment->currency,
        ]);
    }

    /**
     * GET /api/areeba/status/{reference}   (poll from the app)
     */
    public function status(Request $request, string $reference)
    {
        $payment = Payment::where('reference', $reference)->first();

        if (!$payment) {
            return parent::return_error('Unknown payment reference', 404, 'Unknown payment reference');
        }

        if (!$payment->isTerminal()) {
            $payment = $this->areeba->verify($payment, null, 'app_status');
        }

        return response()->json([
            'status'        => $payment->status,
            'paid'          => $payment->isPaid(),
            'status_reason' => $payment->status_reason,
            'receipt_url'   => $payment->isPaid() ? \App\Http\Controllers\PaymentController::receiptUrl($payment) : null,
            'amount'        => $payment->amount,
            'currency'      => $payment->currency,
            'paid_at'       => optional($payment->paid_at)->toIso8601String(),
        ]);
    }
}
