@php
	/** @var \App\Models\Payment $payment */
	$ar = $locale === 'ar';
	$rows = array_filter([
		[$ar ? 'رقم الإيصال' : 'Receipt number', $payment->reference],
		[$ar ? 'رقم معاملة أريبا' : 'Areeba transaction ID', $payment->areeba_transaction_id],
		[$ar ? 'تاريخ ووقت الدفع' : 'Payment date & time', optional($payment->paid_at)->timezone('Asia/Baghdad')->format('Y-m-d H:i')],
		[$ar ? 'اسم المقترض' : 'Borrower name', $payment->borrower_name_masked],
		[$ar ? 'رقم القرض' : 'Loan number', $payment->loan_number],
		[$ar ? 'الفرع' : 'Branch', $payment->branch_name ?: $payment->branch_code],
		[$ar ? 'نوع القرض' : 'Loan product', $payment->loan_product],
		[$ar ? 'تاريخ الاستحقاق' : 'Due date', optional($payment->due_date)->format('Y-m-d')],
		[$ar ? 'طريقة الدفع' : 'Payment method', $ar ? 'بطاقة عبر بوابة أريبا' : 'Card via Areeba payment gateway'],
	], fn ($row) => $row[1] !== null && $row[1] !== '');
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $ar ? 'rtl' : 'ltr' }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<title>{{ $ar ? 'إيصال دفع' : 'Payment receipt' }} {{ $payment->reference }} | Vitas</title>
	<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/favicon/favicon-32x32.png') }}">
	<style>
		:root{--primary:#C1CD21;--secondary:#4D4D4F}
		*{box-sizing:border-box}
		body{margin:0;background:#f4f5f7;color:#212529;font-family:Verdana,Tahoma,Arial,sans-serif;font-size:14px}
		.sheet{max-width:640px;margin:32px auto;background:#fff;border-top:6px solid var(--primary);padding:32px 36px;box-shadow:0 2px 14px rgba(0,0,0,.07)}
		.head{display:flex;justify-content:space-between;align-items:center;gap:16px;border-bottom:1px solid #e9ecef;padding-bottom:20px;margin-bottom:22px}
		.head img{height:44px}
		.head h1{font-size:18px;margin:0;color:var(--secondary)}
		.paid{display:inline-block;background:#e8f5e9;color:#1e7b34;font-weight:bold;border-radius:4px;padding:4px 10px;font-size:12px;margin-top:6px}
		.amount{text-align:center;margin:8px 0 26px}
		.amount small{display:block;color:#6c757d;margin-bottom:4px}
		.amount strong{font-size:30px;color:#000}
		table{width:100%;border-collapse:collapse}
		th,td{padding:10px 0;border-bottom:1px solid #f0f0f0;vertical-align:top;text-align:start}
		th{font-weight:normal;color:#6c757d;width:42%}
		td{font-weight:bold;word-break:break-word}
		.foot{margin-top:24px;color:#6c757d;font-size:12px;line-height:1.6}
		.actions{max-width:640px;margin:0 auto 32px;display:flex;gap:10px;justify-content:center}
		.actions button,.actions a{border:1px solid var(--primary);background:var(--primary);color:#fff;border-radius:6px;padding:11px 22px;font-size:13px;font-weight:bold;cursor:pointer;text-decoration:none;font-family:inherit}
		.actions a{background:#fff;color:var(--secondary);border-color:var(--secondary)}
		@media (max-width:600px){.sheet{margin:0;padding:24px 18px;box-shadow:none}.head{flex-direction:column;align-items:flex-start}}
		@media print{body{background:#fff}.sheet{margin:0;box-shadow:none;max-width:none}.actions{display:none}}
	</style>
</head>
<body>
	<div class="sheet">
		<div class="head">
			<img src="{{ asset('assets/images/logo.png') }}" alt="Vitas">
			<div>
				<h1>{{ $ar ? 'إيصال دفع رسمي' : 'Official payment receipt' }}</h1>
				<span class="paid">{{ $ar ? 'مدفوع' : 'PAID' }}</span>
			</div>
		</div>

		<div class="amount">
			<small>{{ $ar ? 'المبلغ المدفوع' : 'Amount paid' }}</small>
			<strong>{{ number_format((float) $payment->amount, 0) }} {{ $payment->currency }}</strong>
		</div>

		<table>
			@foreach($rows as [$label, $value])
				<tr><th>{{ $label }}</th><td>{{ $value }}</td></tr>
			@endforeach
		</table>

		<p class="foot">
			{{ $ar ? 'هذا إيصال إلكتروني صادر عن فيتاس لتأكيد استلام الدفعة أعلاه. يرجى الاحتفاظ به لسجلاتك.' : 'This is an electronic receipt issued by Vitas confirming receipt of the payment above. Please keep it for your records.' }}
		</p>
	</div>

	<div class="actions">
		<button type="button" onclick="window.print()">{{ $ar ? 'طباعة / حفظ PDF' : 'Print / Save as PDF' }}</button>
		<a href="{{ route('home', $locale) }}">{{ $ar ? 'العودة إلى الموقع' : 'Back to website' }}</a>
	</div>
</body>
</html>
