<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\Areeba\AreebaException;
use App\Services\Areeba\AreebaPaymentService;
use App\Services\VitasCoreClient;
use Illuminate\Http\Request;

/**
 * Public website flow: a payer enters a loan number, we look up the
 * outstanding installment, then redirect to Areeba and show the result.
 * No login — identification is the loan number only (per product decision).
 */
class PaymentController extends Controller
{
    public function __construct(
        private AreebaPaymentService $areeba,
        private VitasCoreClient $core
    ) {
    }

    /** GET /{locale}/pay/loan */
    public function showLoanForm(Request $request, string $locale)
    {
        return view('payment.loan-form', [
            'locale' => $locale,
            'error'  => $request->session()->get('pay_error'),
            'old'    => $request->old(),
        ]);
    }

    /** POST /{locale}/pay/loan */
    public function lookupAndInitiate(Request $request, string $locale)
    {
        $data = $request->validate([
            'loan_number' => ['required', 'string', 'max:64'],
        ]);

        $inquiry = $this->core->customerDataInquiry(trim($data['loan_number']));

        if (!$this->core->hasPayableInstallment($inquiry)) {
            return back()
                ->withInput()
                ->with('pay_error', $this->t($locale,
                    'No outstanding installment was found for this loan number.',
                    'لم يتم العثور على قسط مستحق لرقم القرض هذا.'));
        }

        try {
            $payment = $this->areeba->startLoanPayment($inquiry, 'web', null);
        } catch (AreebaException $e) {
            return back()
                ->withInput()
                ->with('pay_error', $this->t($locale,
                    'We could not start the payment. Please try again shortly.',
                    'تعذّر بدء عملية الدفع. يرجى المحاولة مرة أخرى بعد قليل.'));
        }

        return redirect()->away($payment->redirect_url);
    }

    /** GET /{locale}/pay/result?reference=... */
    public function paymentReturn(Request $request, string $locale)
    {
        $payment = Payment::where('reference', $request->query('reference'))->first();

        if ($payment && $payment->channel === 'web') {
            $payment = $this->areeba->syncFromGateway($payment);
        }

        return view('payment.web-result', [
            'locale'  => $locale,
            'payment' => $payment,
            'status'  => $payment->status ?? 'unknown',
        ]);
    }

    private function t(string $locale, string $en, string $ar): string
    {
        return $locale === 'ar' ? $ar : $en;
    }
}
