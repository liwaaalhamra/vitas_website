@php
    /** @var \App\Models\Payment|null $payment */
    $ar = ($locale ?? app()->getLocale()) === 'ar';
    $map = [
        'paid'      => [$ar ? 'تم الدفع بنجاح' : 'Payment successful',      $ar ? 'تم استلام دفعتك. شكرًا لك.' : 'Your payment has been received. Thank you.', '#137333', '✓'],
        'pending'   => [$ar ? 'الدفع قيد المعالجة' : 'Payment processing',   $ar ? 'سنؤكد العملية خلال لحظات.' : 'We will confirm the payment shortly.', '#8a6d00', '…'],
        'redirected'=> [$ar ? 'الدفع قيد المعالجة' : 'Payment processing',   $ar ? 'سنؤكد العملية خلال لحظات.' : 'We will confirm the payment shortly.', '#8a6d00', '…'],
        'failed'    => [$ar ? 'فشلت عملية الدفع' : 'Payment failed',         $ar ? 'لم تكتمل العملية. لم يتم خصم أي مبلغ.' : 'The payment did not go through. You have not been charged.', '#c5221f', '✕'],
        'declined'  => [$ar ? 'تم رفض البطاقة' : 'Card declined',            $ar ? 'يرجى المحاولة ببطاقة أخرى.' : 'Please try again with a different card.', '#c5221f', '✕'],
        'cancelled' => [$ar ? 'تم إلغاء الدفع' : 'Payment cancelled',        $ar ? 'يمكنك إعادة المحاولة في أي وقت.' : 'You can try again any time.', '#5f6368', '✕'],
        'expired'   => [$ar ? 'انتهت صلاحية الجلسة' : 'Session expired',      $ar ? 'يرجى بدء عملية دفع جديدة.' : 'Please start a new payment.', '#c5221f', '✕'],
        'mismatch'  => [$ar ? 'تعذّر التحقق من الدفع' : 'Payment not verified',$ar ? 'يرجى التواصل مع الدعم مع الاحتفاظ بالمرجع أدناه.' : 'Please contact support with the reference below.', '#c5221f', '!'],
        'error'     => [$ar ? 'حدث خطأ' : 'Something went wrong',            $ar ? 'يرجى المحاولة لاحقًا.' : 'Please try again later.', '#c5221f', '!'],
        'unknown'   => [$ar ? 'الدفع غير معروف' : 'Payment not found',       $ar ? 'تعذّر العثور على هذه العملية.' : 'We could not find this payment.', '#5f6368', '?'],
    ];
    [$title, $body, $color, $icon] = $map[$status] ?? $map['unknown'];
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $ar ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} | Vitas</title>
    @if(in_array($status, ['pending','redirected']))
        <meta http-equiv="refresh" content="6">
    @endif
    <style>
        html,body{margin:0;min-height:100%;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;background:#f4f5f7;color:#202124}
        .wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;box-sizing:border-box}
        .card{background:#fff;border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,.08);padding:36px 30px;max-width:420px;width:100%;text-align:center}
        .badge{width:68px;height:68px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:34px;color:#fff;margin:0 auto 20px}
        h1{font-size:20px;margin:0 0 10px}
        p{font-size:14px;color:#5f6368;margin:6px 0}
        .amount{font-size:16px;color:#202124;font-weight:600;margin-top:14px}
        .ref{margin-top:18px;font-size:12px;color:#9aa0a6;word-break:break-all}
        a.btn{display:inline-block;margin-top:22px;padding:12px 22px;background:#c5221f;color:#fff;text-decoration:none;border-radius:10px;font-size:14px;font-weight:600}
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <div class="badge" style="background:{{ $color }}">{{ $icon }}</div>
        <h1>{{ $title }}</h1>
        <p>{{ $body }}</p>

        @if($payment)
            <div class="amount">{{ number_format((float) $payment->amount, 2) }} {{ $payment->currency }}</div>
            @if($payment->loan_number)
                <p>{{ $ar ? 'رقم القرض' : 'Loan number' }}: {{ $payment->loan_number }}</p>
            @endif
            <p class="ref">{{ $ar ? 'المرجع' : 'Reference' }}: {{ $payment->reference }}</p>
        @endif

        @if(in_array($status, ['failed','declined','cancelled','expired','error','unknown']))
            <a class="btn" href="{{ route('pay.loan.form', ['locale' => $locale]) }}">{{ $ar ? 'إعادة المحاولة' : 'Try again' }}</a>
        @endif
    </div>
</div>
</body>
</html>
