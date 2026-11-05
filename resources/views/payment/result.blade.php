@extends('layouts/main')
@php
	/** @var \App\Models\Payment $payment */
	$ar = $locale === 'ar';
	$status = $payment->status;
	$map = [
		'paid'            => [$ar ? 'تم الدفع بنجاح' : 'Payment successful', $ar ? 'تم استلام دفعتك. شكرًا لك.' : 'Your payment has been received. Thank you.', 'success'],
		'session_created' => [$ar ? 'الدفع قيد المعالجة' : 'Payment processing', $ar ? 'نقوم بتأكيد عملية الدفع، ستتحدث هذه الصفحة تلقائيًا.' : 'We are confirming your payment, this page will refresh automatically.', 'warning'],
		'initiated'       => [$ar ? 'الدفع قيد المعالجة' : 'Payment processing', $ar ? 'نقوم بتأكيد عملية الدفع، ستتحدث هذه الصفحة تلقائيًا.' : 'We are confirming your payment, this page will refresh automatically.', 'warning'],
		'failed'          => [$ar ? 'لم تتم عملية الدفع' : 'Payment failed', $ar ? 'لم تكتمل عملية الدفع ولم يتم خصم أي مبلغ. يمكنك المحاولة مرة أخرى.' : 'The payment did not go through and you have not been charged. You can try again.', 'danger'],
		'cancelled'       => [$ar ? 'تم إلغاء الدفع' : 'Payment cancelled', $ar ? 'تم إلغاء عملية الدفع ولم يتم خصم أي مبلغ. يمكنك المحاولة مرة أخرى.' : 'The payment was cancelled and you have not been charged. You can try again.', 'secondary'],
	];
	[$title, $body, $tone] = $map[$status] ?? $map['failed'];
	$open = in_array($status, \App\Models\Payment::OPEN_STATUSES, true);
@endphp

@push('head')
	@if($open)<meta http-equiv="refresh" content="8">@endif
@endpush

@section ('content')
<div class="py-5">
	<div class="container py-3">
		<div class="bg-grey px-3 py-5">
			<div class="text-center mx-auto" style="max-width:560px">
				<div class="alert alert-{{ $tone }} mb-4" role="status"><b>{{ $title }}</b></div>
				<p class="mb-4">{{ $body }}</p>

				<p class="mb-1">{{ $ar ? 'المبلغ' : 'Amount' }}: <b class="text-black">{{ number_format((float) $payment->amount, 0) }} {{ $payment->currency }}</b></p>
				<p class="mb-1">{{ $ar ? 'رقم القرض' : 'Loan number' }}: <b class="text-black">{{ $payment->loan_number }}</b></p>
				<p class="mb-4 small text-muted">{{ $ar ? 'مرجع العملية' : 'Payment reference' }}: {{ $payment->reference }}</p>

				@if($receiptUrl)
					<a class="btn btn-custom btn-filled" href="{{ $receiptUrl }}"><b>{{ $ar ? 'عرض الإيصال الرقمي' : 'View digital receipt' }}</b></a>
				@elseif(!$open)
					<a class="btn btn-custom btn-filled" href="{{ route('payment.index', $locale) }}"><b>{{ $ar ? 'إعادة المحاولة' : 'Try again' }}</b></a>
				@endif
			</div>
		</div>
	</div>
</div>
@endsection
