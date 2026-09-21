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
 * auth + core-lookup flow of VitasController@zain_cash.
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
        $inquiry = $this->core->customerDataInquiry((string) $loanNumber);

        if (!$this->core->hasPayableInstallment($inquiry)) {
            return parent::return_error('No payable installment for this loan', 459, 'No payable installment for this loan');
        }

        // 3. Open the Areeba transaction.
        try {
            $payment = $this->areeba->startLoanPayment($inquiry, 'app', (int) $user->id);
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
     * GET|POST /api/areeba/callback  (server-to-server notification from Areeba)
     * Always answers "OK" so the gateway stops retrying; reconciliation happens
     * against the status API.
     */
    public function callback(Request $request)
    {
        $this->areeba->handleCallback($request);

        return response('OK', 200)->header('Content-Type', 'text/plain');
    }

    /**
     * GET /api/areeba/return?reference=...   (browser lands here after paying)
     * Renders a tiny page the in-app browser can detect; ?format=json for a
     * machine-readable payload.
     */
    public function paymentReturn(Request $request)
    {
        $payment = Payment::where('reference', $request->query('reference'))->first();

        if ($payment) {
            $payment = $this->areeba->syncFromGateway($payment);
        }

        $status = $payment->status ?? 'unknown';

        if ($request->query('format') === 'json') {
            return response()->json([
                'status'    => $status,
                'paid'      => $status === 'paid',
                'reference' => $payment->reference ?? null,
            ]);
        }

        return response()->view('payment.app-result', [
            'payment' => $payment,
            'status'  => $status,
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
            $payment = $this->areeba->syncFromGateway($payment);
        }

        return response()->json([
            'status'        => $payment->status,
            'paid'          => $payment->isPaid(),
            'status_reason' => $payment->status_reason,
            'amount'        => $payment->amount,
            'currency'      => $payment->currency,
            'paid_at'       => optional($payment->paid_at)->toIso8601String(),
        ]);
    }
}
