<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\Areeba\AreebaPaymentService;
use Illuminate\Http\Request;

/**
 * Endpoints Areeba calls back on (no locale prefix):
 *   GET|POST /payment/return   customer browser after the hosted checkout
 *   POST     /payment/webhook  server-to-server notification (HMAC signed)
 */
class PaymentReturnController extends Controller
{
    public function __construct(private AreebaPaymentService $areeba)
    {
        // Skip the base admin-section bootstrapping.
    }

    public function return(Request $request)
    {
        $reference = $request->input('ref') ?: $request->input('pt');
        $payment = $reference ? Payment::where('reference', $reference)->first() : null;

        if (!$payment) {
            return redirect()->route('payment.index', ['locale' => 'ar']);
        }

        $payment = $this->areeba->handleReturn($payment, $request);

        if ($payment->channel === 'app') {
            return response()->view('payment.app-result', ['payment' => $payment, 'status' => $payment->status]);
        }

        return redirect()->to(PaymentController::resultUrl($payment));
    }

    public function webhook(Request $request)
    {
        [$status, $body] = $this->areeba->handleWebhook($request);

        return response($body, $status)->header('Content-Type', 'text/plain');
    }
}
