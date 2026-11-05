<?php

namespace App\Services;

use App\Models\Payment;
use App\Support\Payload;
use App\Support\Secret;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Vitas Core Banking (web-abacus):
 *   - CustomerDataInquiryByLoanNumber: outstanding installment for a loan number
 *   - CustomerPaymentDetails:          settle a paid installment in the core
 * Every call is appended to api_logs.
 */
class VitasCoreClient
{
    const SERVICE = 'core_banking';

    public function __construct(private ApiLogger $logger)
    {
    }

    /**
     * @return array{found: bool, data: ?array, message: ?string}
     *   data (normalized): loan_number, customer_name, phone_number, branch_name, branch_code,
     *   loan_product, due_date, amount_iqd, client_id, installment_number, raw
     */
    public function inquiry(string $loanNumber): array
    {
        $cfg = config('services.vitas_core');

        $result = $this->logger->send($this->request(), self::SERVICE, 'inquiry', 'POST', $this->url($cfg['inquiry_path']), [
            'json' => [
                'LoanNumber' => $loanNumber,
                'username'   => $cfg['inquiry_username'],
                'password'   => Secret::reveal($cfg['inquiry_password']),
            ],
        ]);

        $json = $result['response'] ? $result['response']->json() : null;
        if (!is_array($json) || $result['response']->failed()) {
            return ['found' => false, 'data' => null, 'message' => $result['error'] ?: 'core banking unavailable', 'unavailable' => true];
        }

        // Core convention: StatusCode 0 = OK, anything else carries an ActionStatus message
        $statusCode = Payload::find($json, ['StatusCode']);
        if ($statusCode !== null && (int) $statusCode !== 0) {
            return ['found' => false, 'data' => null, 'message' => Payload::find($json, ['ActionStatus', 'Message']) ?: 'loan not found'];
        }

        $data = $this->normalizeInquiry($json, $loanNumber);
        if ($data['amount_iqd'] <= 0) {
            return ['found' => false, 'data' => $data, 'message' => 'no amount due'];
        }

        return ['found' => true, 'data' => $data, 'message' => null];
    }

    /**
     * Push a paid installment to the core.
     *
     * @return array{ok: bool, http_status: ?int, status_code: ?string, action_status: ?string, body: mixed}
     */
    public function pushPayment(Payment $payment): array
    {
        $cfg = config('services.vitas_core');

        $result = $this->logger->send($this->request(), self::SERVICE, 'payment_push', 'POST', $this->url($cfg['payment_path']), [
            'json' => [
                'LoanNumber'       => (string) $payment->loan_number,
                'DueAmount'        => number_format((float) $payment->amount, 2, '.', ''),
                'PaidAmount'       => number_format((float) $payment->amount, 2, '.', ''),
                'TransxsID'        => (string) ($payment->areeba_transaction_id ?: $payment->reference),
                'PaymentDate'      => ($payment->paid_at ?: now())->format('Y-m-d\TH:i:s'),
                'username'         => $cfg['payment_username'],
                'password'         => Secret::reveal($cfg['payment_password']),
                'SourceCurrencyID' => (int) $cfg['source_currency_id'],
                'UserId'           => '',
            ],
        ], $payment->id);

        $response = $result['response'];
        $json = $response ? $response->json() : null;
        $statusCode = is_array($json) ? Payload::find($json, ['StatusCode']) : null;
        $actionStatus = is_array($json) ? Payload::find($json, ['ActionStatus', 'Message']) : null;

        // Success = HTTP 2xx and StatusCode 0 (or absent)
        $ok = $response && $response->successful() && ($statusCode === null || (int) $statusCode === 0);

        return [
            'ok'            => $ok,
            'http_status'   => $response ? $response->status() : null,
            'status_code'   => $statusCode !== null ? (string) $statusCode : null,
            'action_status' => $actionStatus !== null ? (string) $actionStatus : $result['error'],
            'body'          => $json ?? ($response ? $response->body() : null),
        ];
    }

    private function normalizeInquiry(array $json, string $loanNumber): array
    {
        $amount = Payload::find($json, ['TotalPaymentDueIQD', 'PaymentDueIQD', 'DueAmountIQD', 'TotalDueAmount', 'DueAmount', 'AmountDue']);
        $dueDate = Payload::find($json, ['DueDate', 'PaymentDueDate', 'NextDueDate', 'InstallmentDueDate']);

        return [
            'loan_number'        => (string) (Payload::find($json, ['LoanNumber']) ?: $loanNumber),
            'customer_name'      => Payload::find($json, ['CustomerName', 'ClientName', 'BorrowerName', 'FullName', 'Name']),
            'phone_number'       => Payload::find($json, ['PhoneNumber', 'MobileNumber', 'Mobile', 'Phone']),
            'branch_name'        => Payload::find($json, ['BranchName', 'Branch']),
            'branch_code'        => Payload::find($json, ['BranchCode', 'BranchID']),
            'loan_product'       => Payload::find($json, ['LoanProduct', 'LoanProductName', 'ProductName', 'Product']),
            'due_date'           => $this->parseDate($dueDate),
            'amount_iqd'         => round((float) str_replace(',', '', (string) $amount), 2),
            'client_id'          => Payload::find($json, ['ClientID', 'ClientId', 'CustomerID']),
            'installment_number' => Payload::find($json, ['PaymentNumber', 'InstallmentNumber']),
            'raw'                => $json,
        ];
    }

    private function parseDate($value): ?string
    {
        if (!$value) {
            return null;
        }
        try {
            return Carbon::parse($value)->toDateString();
        } catch (Throwable $e) {
            return null;
        }
    }

    private function request()
    {
        return Http::acceptJson()->timeout((int) config('services.vitas_core.timeout', 30))->connectTimeout(10);
    }

    private function url(string $path): string
    {
        return rtrim(config('services.vitas_core.base_url'), '/').'/'.ltrim($path, '/');
    }
}
