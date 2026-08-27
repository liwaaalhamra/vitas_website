<style>
    body, html {
        height: 100%;
        width: 100%;
        margin: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        background: #FFFFFF; /* White background */
        font-family: 'Arial', sans-serif;
    }

    .content-wrapper {
        text-align: center;
        margin: auto;
        width: 100%; /* Full width */
    }

    .card {
        background-color: #FCFCFC;
        border-radius: 6px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        padding: 4rem; /* Adjusted padding */
        margin: 15px;
        width: 100%;
        max-width: 600px; /* Increased max card width */
        box-sizing: border-box;
    }

    @media (max-width: 768px) {
        .content-wrapper {
            padding: 1rem;
        }
        .card {
            padding: 2rem; /* Increased padding for larger screens */
            margin: 0;
        }
    }

    h1 {
        font-size: 18px;
        color: #333;
        margin: 2rem 0;
    }

    .info-text, .small-text {
        color: #666;
        font-size: 14px;
        line-height: 1.4;
        margin: 1.5rem 0;
    }

    .small-text {
        font-size: 12px;
        margin-top: 20px;
    }

    input[type="text"] {
        border: 1px solid #ccc;
        border-radius: 4px;
        padding: 10px;
        width: 100%;
        margin-bottom: 10px;
    }

    .action-btn {
        background-color: #d9534f;
        border: none;
        border-radius: 4px;
        color: white;
        cursor: pointer;
        padding: 10px 20px;
        width: 100%;
        box-sizing: border-box; /* Ensures padding doesn't affect width */
        margin-bottom: 10px;
    }

    .action-btn:hover {
        background-color: #c9302c;
    }
</style>

@extends('layouts_delete.main')

@section('content')
    <div class="content-wrapper">
        <div class="card">
            @if(session('account_deleted'))
                <p class="info-text">The delete request for your account has been received successfully</p>
{{--            @elseif(session('otp_sent'))--}}
{{--                <h1>We're sorry to see you leaving</h1>--}}
{{--                <p class="info-text">Please enter the OTP sent to the phone number ending with +961 *** 123</p>--}}
{{--                <form method="POST" action="{{ route('accounts.delete', $locale) }}">--}}
{{--                    @csrf--}}
{{--                    @method('DELETE')--}}
{{--                    <input type="hidden" name="action" value="verify_otp">--}}
{{--                    <input type="text" name="otp" placeholder="OTP" required>--}}
{{--                    <button type="submit" class="action-btn">Delete Account</button>--}}
{{--                </form>--}}
{{--                <p class="small-text">Deleting your account will remove all your information from our database. This cannot be undone.</p>--}}
            @else
                <h1>We're sorry to see you leave our support team will contact you later</h1>
                <p class="info-text">Enter your phone number or email to delete your account</p>
                <form method="POST" action="{{ route('accounts.delete', $locale) }}">
                    @csrf
                    <input type="hidden" name="action" value="send_otp">
                    <input type="text" name="contact" placeholder="Phone number or email" required>
                    <button type="submit" class="action-btn">Submit</button>
                </form>
                <p class="small-text">Deleting your account will remove all your information from our database. This cannot be undone.</p>
            @endif
        </div>
    </div>
@endsection

<script>
    function submitOTP() {
        const form = document.getElementById('delete-account-form');
        if (!form.querySelector('input[name="_method"]')) {
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
        }
        form.submit();
    }
</script>
