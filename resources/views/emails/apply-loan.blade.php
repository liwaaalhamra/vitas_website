You received a message from : {{ $full_name }}

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

@if(isset($home_improvement) && $home_improvement)
<p>
<b>Home Improvement:</b> {{ $home_improvement }}
</p>
@endif

<p>
<b>Platform:</b> {{ $platform }}
</p>
