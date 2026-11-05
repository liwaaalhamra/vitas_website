<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\Areeba\AreebaException;
use App\Services\Areeba\AreebaPaymentService;
use App\Services\VitasCoreClient;
use App\Support\Mask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

/**
 * Public website payment flow (/{locale}/payment):
 *   1. inquiry   loan number -> core banking, masked details back to the browser
 *   2. checkout  initiated order -> Areeba hosted checkout
 *   3. result    after Areeba redirects back (see PaymentReturnController)
 *   4. receipt   printable digital receipt for paid payments
 *
 * The unmasked inquiry never leaves the server: it is kept in the session
 * between steps 1 and 2, so the browser cannot alter the amount either.
 */
class PaymentController extends Controller
{
    const SESSION_KEY = 'payment_inquiry';
    const SESSION_TTL_MINUTES = 10;

    public function __construct(
        private AreebaPaymentService $areeba,
        private VitasCoreClient $core
    ) {
        // Skip the base admin-section bootstrapping.
    }

    /** GET /{locale}/payment */
    public function index(Request $request, string $locale)
    {
        return view('payment.index', [
            'locale'    => $locale,
            'pageTitle' => $this->t($locale, 'Pay your loan', 'دفع القسط'),
        ]);
    }

    /** POST /{locale}/payment/inquiry  (rate limited) */
    public function inquiry(Request $request, string $locale)
    {
        $data = $request->validate([
            'loan_number' => ['required', 'string', 'regex:/^[A-Za-z0-9\-]{3,30}$/'],
        ], [
            'loan_number.required' => $this->t($locale, 'Please enter your loan number.', 'يرجى إدخال رقم القرض.'),
            'loan_number.regex'    => $this->t($locale, 'Please enter a valid loan number.', 'يرجى إدخال رقم قرض صحيح.'),
        ]);

        $request->session()->forget(self::SESSION_KEY);
        $result = $this->core->inquiry(trim($data['loan_number']));

        if (!empty($result['unavailable'])) {
            return response()->json(['message' => $this->t($locale,
                'The payment service is temporarily unavailable. Please try again shortly.',
                'خدمة الدفع غير متاحة مؤقتًا. يرجى المحاولة بعد قليل.')], 503);
        }
        if (!$result['found']) {
            return response()->json(['message' => $this->t($locale,
                'No amount due was found for this loan number. Please check the number and try again.',
                'لم يتم العثور على مبلغ مستحق لرقم القرض هذا. يرجى التحقق من الرقم والمحاولة مرة أخرى.')], 404);
        }

        $inquiry = $result['data'];
        $request->session()->put(self::SESSION_KEY, [
            'data'       => $inquiry,
            'expires_at' => now()->addMinutes(self::SESSION_TTL_MINUTES)->timestamp,
        ]);

        return response()->json([
            'loan_number'  => $inquiry['loan_number'],
            'name'         => Mask::name($inquiry['customer_name']),
            'phone'        => Mask::phone($inquiry['phone_number']),
            'branch'       => $inquiry['branch_name'] ?: $inquiry['branch_code'],
            'loan_product' => $inquiry['loan_product'],
            'due_date'     => $inquiry['due_date'],
            'amount'       => number_format($inquiry['amount_iqd'], 0),
            'currency'     => config('services.areeba.currency', 'IQD'),
        ]);
    }

    /** POST /{locale}/payment/checkout  (rate limited) */
    public function checkout(Request $request, string $locale)
    {
        $held = $request->session()->get(self::SESSION_KEY);

        if (!$held || ($held['expires_at'] ?? 0) < now()->timestamp) {
            $request->session()->forget(self::SESSION_KEY);

            return response()->json(['message' => $this->t($locale,
                'Your session has expired. Please enter your loan number again.',
                'انتهت صلاحية الجلسة. يرجى إدخال رقم القرض مرة أخرى.')], 422);
        }

        try {
            $payment = $this->areeba->startCheckout($held['data'], 'web', null, $locale);
        } catch (AreebaException $e) {
            return response()->json(['message' => $this->t($locale,
                'We could not start the payment. Please try again shortly.',
                'تعذّر بدء عملية الدفع. يرجى المحاولة مرة أخرى بعد قليل.')], 502);
        }

        $request->session()->forget(self::SESSION_KEY);

        return response()->json(['redirect_url' => $payment->redirect_url]);
    }

    /** GET /{locale}/payment/result/{reference}  (signed) */
    public function result(Request $request, string $locale, string $reference)
    {
        $payment = Payment::where('reference', $reference)->where('channel', 'web')->firstOrFail();

        if (!$payment->isTerminal() && $payment->areeba_transaction_id) {
            $payment = $this->areeba->verify($payment, null, 'result_page');
        }

        return view('payment.result', [
            'locale'     => $locale,
            'pageTitle'  => $this->t($locale, 'Payment result', 'نتيجة الدفع'),
            'payment'    => $payment,
            'receiptUrl' => $payment->isPaid() ? self::receiptUrl($payment, $locale) : null,
        ]);
    }

    /** GET /{locale}/payment/receipt/{reference}  (signed) */
    public function receipt(Request $request, string $locale, string $reference)
    {
        $payment = Payment::where('reference', $reference)->where('status', 'paid')->firstOrFail();

        return view('payment.receipt', ['locale' => $locale, 'payment' => $payment]);
    }

    /** Old /pay/loan entry point from the first Areeba integration. */
    public function legacyForm(string $locale)
    {
        return redirect()->route('payment.index', ['locale' => $locale], 301);
    }

    public static function resultUrl(Payment $payment, ?string $locale = null): string
    {
        return URL::signedRoute('payment.result', ['locale' => $locale ?: ($payment->locale ?: 'ar'), 'reference' => $payment->reference]);
    }

    public static function receiptUrl(Payment $payment, ?string $locale = null): string
    {
        return URL::signedRoute('payment.receipt', ['locale' => $locale ?: ($payment->locale ?: 'ar'), 'reference' => $payment->reference]);
    }

    private function t(string $locale, string $en, string $ar): string
    {
        return $locale === 'ar' ? $ar : $en;
    }
}
