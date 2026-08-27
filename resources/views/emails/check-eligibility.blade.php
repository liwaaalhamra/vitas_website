You received a message from {{ $full_name }}, 
<p>
<b>
{{$msg}}
</b>
</p>

<p>
<b>Full Name:</b> {{ $full_name }}
</p>

<p>
<b>Age:</b> {{ $age }}
</p>

<p>
<b>Phone Number:</b> {{ $phone_number }}
</p>

<p>
<b>Email Address:</b> {{ $email_address }}
</p>

<p>
<b>Governorate:</b> {{ $governorate }}
</p>

<p>
<b>Loan Type:</b> {{ $loan_type }}
</p>

<p>
<b>Business Owner:</b> {{ $business_owner }}
</p>

<p>
<b>Employee:</b> {{ $employee }}
</p>

@if(isset($disability) && $disability)
<p>
<b>Disability:</b> {{ $disability }}
</p>
@endif

@if(isset($own_home) && $own_home)
<p>
<b>Own Home:</b> {{ $own_home }}
</p>
@endif

@if(isset($guarantors) && $guarantors)
<p>
<b>Guarantors:</b> {{ $guarantors }}
</p>
@endif
@if(isset($monthly_expenses) && $monthly_expenses)
<p>
<b>Monthly Expenses:</b> {{ $monthly_expenses }}
</p>
@endif
@if(isset($monthly_income) && $monthly_income)
<p>
<b>Monthly Income:</b> {{ $monthly_income }}
</p>
@endif
<p>
<b>Platform:</b> {{ $platform }}
</p>
