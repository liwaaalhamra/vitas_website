@extends('layouts/main')
@php $ar = $locale === 'ar'; @endphp

@push('head')
<style>
	.pay-card{max-width:560px;margin:0 auto}
	.pay-summary{width:100%;margin:0}
	.pay-summary th{font-weight:normal;color:#6c757d;padding:.55rem 0;width:45%;vertical-align:top}
	.pay-summary td{padding:.55rem 0;font-weight:bold;color:#000;word-break:break-word}
	.pay-summary tr+tr th,.pay-summary tr+tr td{border-top:1px solid #e9ecef}
	.pay-amount{font-size:1.6rem;color:#000}
	.pay-secure{font-size:.8rem;color:#6c757d}
	#payment-inquiry .alert,#payment-modal .alert{display:none}
</style>
@endpush

@section ('content')
<div class="py-5">
	<div class="container py-3">
		<h3 class="text-black text-center mb-2"><b>{{ $ar ? 'دفع قسط القرض' : 'Pay your loan installment' }}</b></h3>
		<p class="text-center mb-5">{{ $ar ? 'أدخل رقم القرض لعرض المبلغ المستحق والدفع بأمان بالبطاقة.' : 'Enter your loan number to see the amount due and pay securely by card.' }}</p>

		<div class="bg-grey px-3 py-5">
			<div class="pay-card">
				<form id="payment-inquiry" method="post" action="{{ route('payment.inquiry', $locale) }}" novalidate>
					@csrf
					<div class="alert alert-danger" role="alert"></div>
					<label for="loan_number" class="text-black"><b>{{ $ar ? 'رقم القرض / مرجع العميل' : 'Loan number / client reference' }}</b></label>
					<input class="form-control form-custom mb-4" id="loan_number" name="loan_number" inputmode="numeric" autocomplete="off" maxlength="30" required
						   placeholder="{{ $ar ? 'مثال: 10023456' : 'e.g. 10023456' }}">
					<button type="submit" class="btn btn-custom btn-filled w-100">
						<b data-text="{{ $ar ? 'متابعة' : 'Continue' }}" data-loading-text="{{ $ar ? 'جارٍ البحث...' : 'Searching...' }}">{{ $ar ? 'متابعة' : 'Continue' }}</b>
					</button>
					<p class="pay-secure text-center mt-3 mb-0">{{ $ar ? 'تتم معالجة الدفع بأمان عبر بوابة أريبا. لا يتم حفظ بيانات بطاقتك لدينا.' : 'Payments are processed securely by Areeba. Your card details never reach our website.' }}</p>
				</form>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="payment-modal" tabindex="-1" role="dialog" aria-labelledby="payment-modal-title" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title text-black" id="payment-modal-title"><b>{{ $ar ? 'تأكيد تفاصيل الدفع' : 'Confirm payment details' }}</b></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="{{ $ar ? 'إغلاق' : 'Close' }}"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<div class="alert alert-danger" role="alert"></div>
				<table class="pay-summary">
					<tr><th>{{ $ar ? 'اسم المقترض' : 'Borrower name' }}</th><td data-field="name"></td></tr>
					<tr><th>{{ $ar ? 'رقم القرض' : 'Loan number' }}</th><td data-field="loan_number"></td></tr>
					<tr data-row="branch"><th>{{ $ar ? 'الفرع' : 'Branch' }}</th><td data-field="branch"></td></tr>
					<tr data-row="loan_product"><th>{{ $ar ? 'نوع القرض' : 'Loan product' }}</th><td data-field="loan_product"></td></tr>
					<tr data-row="due_date"><th>{{ $ar ? 'تاريخ الاستحقاق' : 'Due date' }}</th><td data-field="due_date"></td></tr>
					<tr><th>{{ $ar ? 'المبلغ المستحق' : 'Amount due' }}</th><td><span class="pay-amount" data-field="amount"></span> <span data-field="currency"></span></td></tr>
				</table>
				<p class="pay-secure mt-3 mb-0">{{ $ar ? 'يرجى التأكد من صحة التفاصيل. سيتم تحويلك إلى صفحة الدفع الآمنة لأريبا.' : 'Please check the details are correct. You will be taken to Areeba\'s secure payment page.' }}</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-custom btn-filled-grey" data-dismiss="modal"><b>{{ $ar ? 'إلغاء' : 'Cancel' }}</b></button>
				<button type="button" class="btn btn-custom btn-filled" id="payment-proceed">
					<b data-text="{{ $ar ? 'المتابعة إلى الدفع الآمن' : 'Proceed to Secure Payment' }}" data-loading-text="{{ $ar ? 'جارٍ التحويل...' : 'Redirecting...' }}">{{ $ar ? 'المتابعة إلى الدفع الآمن' : 'Proceed to Secure Payment' }}</b>
				</button>
			</div>
		</div>
	</div>
</div>
@endsection

@push('scripts')
<script>
(function ($) {
	var $form = $('#payment-inquiry'), $modal = $('#payment-modal');
	var token = $form.find('input[name=_token]').val();
	var genericError = @json($ar ? 'حدث خطأ. يرجى المحاولة مرة أخرى.' : 'Something went wrong. Please try again.');
	var tooMany = @json($ar ? 'محاولات كثيرة. يرجى الانتظار دقيقة ثم المحاولة مرة أخرى.' : 'Too many attempts. Please wait a minute and try again.');

	function loading($btn, on) {
		var $label = $btn.find('b');
		$btn.prop('disabled', on);
		$label.text(on ? $label.data('loading-text') : $label.data('text'));
	}
	function showError($box, xhr) {
		var message = genericError;
		if (xhr.status === 429) {
			message = tooMany;
		} else if (xhr.responseJSON) {
			var errors = xhr.responseJSON.errors;
			message = errors ? errors[Object.keys(errors)[0]][0] : (xhr.responseJSON.message || genericError);
		}
		$box.text(message).show();
	}

	$form.on('submit', function (e) {
		e.preventDefault();
		var $btn = $form.find('button[type=submit]'), $alert = $form.find('.alert').hide();
		loading($btn, true);

		$.ajax({url: $form.attr('action'), method: 'POST', dataType: 'json', headers: {'X-CSRF-TOKEN': token},
			data: {loan_number: $.trim($('#loan_number').val())}})
			.done(function (data) {
				$.each(data, function (key, value) {
					$modal.find('[data-field=' + key + ']').text(value || '');
					$modal.find('[data-row=' + key + ']').toggle(!!value);
				});
				$modal.find('.alert').hide();
				$modal.modal('show');
			})
			.fail(function (xhr) { showError($alert, xhr); })
			.always(function () { loading($btn, false); });
	});

	$('#payment-proceed').on('click', function () {
		var $btn = $(this), $alert = $modal.find('.alert').hide();
		loading($btn, true);

		$.ajax({url: @json(route('payment.checkout', $locale)), method: 'POST', dataType: 'json', headers: {'X-CSRF-TOKEN': token}})
			.done(function (data) { window.location.href = data.redirect_url; })
			.fail(function (xhr) { showError($alert, xhr); loading($btn, false); });
	});
})(jQuery);
</script>
@endpush
