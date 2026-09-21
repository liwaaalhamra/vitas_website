<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Talks to the Vitas core (web-abacus) "CustomerDataInquiry" endpoint that
 * returns the outstanding installment for a loan number. This is the same
 * endpoint + credentials the legacy ZainCash flow uses.
 */
class VitasCoreClient
{
    /**
     * @return array|null  Decoded inquiry payload, or null when the call fails
     *                      or the response is not a usable object.
     */
    public function customerDataInquiry(string $loanNumber): ?array
    {
        $cfg = config('services.vitas_core');

        try {
            $response = Http::asForm()
                ->timeout(30)
                ->post($cfg['inquiry_url'], [
                    'loanNumber' => $loanNumber,
                    'username'   => $cfg['username'],
                    'password'   => $cfg['password'],
                ]);
        } catch (\Throwable $e) {
            Log::channel('payments')->error('vitas_core.inquiry.transport_error', [
                'loan' => $loanNumber,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        $data = $response->json();

        Log::channel('payments')->info('vitas_core.inquiry.response', [
            'loan'   => $loanNumber,
            'status' => $response->status(),
            'ok'     => is_array($data),
        ]);

        return is_array($data) ? $data : null;
    }

    /**
     * True when the inquiry payload actually carries a payable installment.
     */
    public function hasPayableInstallment(?array $inquiry): bool
    {
        if (!$inquiry) {
            return false;
        }

        $usd = (float) ($inquiry['TotalPaymentDueUSD'] ?? $inquiry['PaymentDueUSD'] ?? 0);
        $iqd = (float) ($inquiry['TotalPaymentDueIQD'] ?? $inquiry['PaymentDueIQD'] ?? 0);

        return ($usd > 0 || $iqd > 0) && !empty($inquiry['LoanNumber']);
    }

    /**
     * Post a settled installment back to the Vitas core so the loan system marks
     * it paid — the Areeba equivalent of the legacy ZainCash
     * CustomerPaymentDetails call. Returns true on a confirmed success.
     */
    public function confirmPayment(Payment $payment): bool
    {
        $cfg = config('services.vitas_core');

        if (!($cfg['confirm_enabled'] ?? true)) {
            Log::channel('payments')->info('vitas_core.confirm.disabled', ['reference' => $payment->reference]);

            return false;
        }

        $body = [
            'LoanNumber'    => (int) $payment->loan_number,
            'DueAmount'     => $payment->amount_usd ?? $payment->amount,
            'PaidAmount'    => $payment->amount_usd ?? $payment->amount,
            'TransxsID'     => $payment->id,
            'Reference'     => $payment->reference,
            'GatewayRef'    => $payment->gateway_uuid,
            'PaymentMethod' => 'Areeba',
            'Channel'       => $payment->channel,
            'PaymentDate'   => optional($payment->paid_at ?? $payment->created_at)->format('Y-m-d H:i:s'),
            'username'      => $cfg['confirm_username'] ?? $cfg['username'],
            'password'      => $cfg['confirm_password'] ?? $cfg['password'],
        ];

        try {
            $response = Http::asForm()->timeout(30)->post($cfg['confirm_url'], $body);
        } catch (\Throwable $e) {
            Log::channel('payments')->error('vitas_core.confirm.transport_error', [
                'reference' => $payment->reference,
                'error'     => $e->getMessage(),
            ]);

            return false;
        }

        $json = $response->json();
        // Success = HTTP 2xx AND (no StatusCode field, or StatusCode == 0).
        $ok = $response->successful()
            && (!is_array($json) || !array_key_exists('StatusCode', $json) || (int) $json['StatusCode'] === 0);

        Log::channel('payments')->info('vitas_core.confirm.response', [
            'reference' => $payment->reference,
            'status'    => $response->status(),
            'ok'        => $ok,
            'body'      => is_array($json) ? $json : (string) $response->body(),
        ]);

        return $ok;
    }
}
