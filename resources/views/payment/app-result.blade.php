@php
    /** @var \App\Models\Payment|null $payment */
    $locale = app()->getLocale();
    $ar = $locale === 'ar';
    $map = [
        'paid'      => [$ar ? 'تم الدفع بنجاح' : 'Payment successful', '#137333', '✓'],
        'pending'   => [$ar ? 'الدفع قيد المعالجة' : 'Payment is processing', '#8a6d00', '…'],
        'redirected'=> [$ar ? 'الدفع قيد المعالجة' : 'Payment is processing', '#8a6d00', '…'],
        'failed'    => [$ar ? 'فشلت عملية الدفع' : 'Payment failed', '#c5221f', '✕'],
        'declined'  => [$ar ? 'تم رفض البطاقة' : 'Card declined', '#c5221f', '✕'],
        'cancelled' => [$ar ? 'تم إلغاء الدفع' : 'Payment cancelled', '#5f6368', '✕'],
        'expired'   => [$ar ? 'انتهت صلاحية الجلسة' : 'Payment session expired', '#c5221f', '✕'],
        'mismatch'  => [$ar ? 'تعذّر التحقق من الدفع' : 'Payment could not be verified', '#c5221f', '!'],
        'error'     => [$ar ? 'حدث خطأ' : 'Something went wrong', '#c5221f', '!'],
        'unknown'   => [$ar ? 'حالة غير معروفة' : 'Unknown status', '#5f6368', '?'],
    ];
    [$label, $color, $icon] = $map[$status] ?? $map['unknown'];
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $ar ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>{{ $label }}</title>
    {{-- Machine-readable hooks for the in-app browser --}}
    <meta name="payment-status" content="{{ $status }}">
    <meta name="payment-reference" content="{{ $payment->reference ?? '' }}">
    <meta name="payment-paid" content="{{ ($status === 'paid') ? 'true' : 'false' }}">
    <style>
        html,body{margin:0;height:100%;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;background:#f4f5f7;color:#202124}
        .wrap{min-height:100%;display:flex;align-items:center;justify-content:center;padding:24px;box-sizing:border-box}
        .card{background:#fff;border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,.08);padding:32px 28px;max-width:360px;width:100%;text-align:center}
        .badge{width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:32px;color:#fff;margin:0 auto 18px}
        h1{font-size:19px;margin:0 0 8px}
        p{font-size:14px;color:#5f6368;margin:4px 0}
        .ref{margin-top:16px;font-size:12px;color:#9aa0a6;word-break:break-all}
    </style>
</head>
<body data-payment-status="{{ $status }}" data-payment-reference="{{ $payment->reference ?? '' }}">
    <div class="wrap">
        <div class="card">
            <div class="badge" style="background:{{ $color }}">{{ $icon }}</div>
            <h1>{{ $label }}</h1>
            @if($payment)
                <p>{{ number_format((float) $payment->amount, 2) }} {{ $payment->currency }}</p>
                @if($payment->loan_number)
                    <p>{{ $ar ? 'رقم القرض' : 'Loan' }}: {{ $payment->loan_number }}</p>
                @endif
                <p class="ref">{{ $payment->reference }}</p>
            @endif
        </div>
    </div>
</body>
</html>
