@php
    $ar = ($locale ?? app()->getLocale()) === 'ar';
    $err = $error ?? null;
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $ar ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $ar ? 'دفع قسط القرض' : 'Pay your loan installment' }} | Vitas</title>
    <style>
        html,body{margin:0;min-height:100%;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;background:#f4f5f7;color:#202124}
        .wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;box-sizing:border-box}
        .card{background:#fff;border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,.08);padding:32px 28px;max-width:420px;width:100%}
        h1{font-size:20px;margin:0 0 6px}
        .sub{font-size:14px;color:#5f6368;margin:0 0 22px}
        label{display:block;font-size:13px;font-weight:600;margin-bottom:6px}
        input[type=text]{width:100%;box-sizing:border-box;padding:12px 14px;border:1px solid #d0d3d7;border-radius:10px;font-size:15px}
        input[type=text]:focus{outline:none;border-color:#c5221f;box-shadow:0 0 0 3px rgba(197,34,31,.12)}
        button{margin-top:18px;width:100%;padding:13px 16px;background:#c5221f;color:#fff;border:0;border-radius:10px;font-size:15px;font-weight:600;cursor:pointer}
        button:hover{background:#a81b19}
        .alert{background:#fce8e6;color:#c5221f;border-radius:10px;padding:12px 14px;font-size:13px;margin-bottom:18px}
        .field-err{color:#c5221f;font-size:12px;margin-top:6px}
        .note{font-size:12px;color:#9aa0a6;margin-top:16px;text-align:center}
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <h1>{{ $ar ? 'دفع قسط القرض' : 'Pay your loan installment' }}</h1>
        <p class="sub">{{ $ar ? 'أدخل رقم القرض لعرض القسط المستحق والدفع عبر البطاقة.' : 'Enter your loan number to see the amount due and pay by card.' }}</p>

        @if($err)
            <div class="alert">{{ $err }}</div>
        @endif

        <form method="POST" action="{{ route('pay.loan.init', ['locale' => $locale]) }}">
            @csrf
            <label for="loan_number">{{ $ar ? 'رقم القرض' : 'Loan number' }}</label>
            <input type="text" id="loan_number" name="loan_number" value="{{ $old['loan_number'] ?? '' }}"
                   autocomplete="off" inputmode="numeric" required>
            @error('loan_number')<div class="field-err">{{ $message }}</div>@enderror

            <button type="submit">{{ $ar ? 'متابعة الدفع' : 'Continue to payment' }}</button>
        </form>

        <p class="note">{{ $ar ? 'المدفوعات تتم بأمان عبر بوابة أريبا.' : 'Payments are processed securely by Areeba.' }}</p>
    </div>
</div>
</body>
</html>
