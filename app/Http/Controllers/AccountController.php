<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function handleOTP(Request $request)
    {
        $page_title = 'Delete Account';

        // Check if the form for sending OTP was submitted
        if ($request->input('action') === 'send_otp') {
            // Logic to send OTP
            // ... (Implement OTP sending logic here)

            // Flash 'otp_sent' to the session to indicate that the OTP has been sent
            return redirect()->back()->with('otp_sent', true)->withInput();
        } // Check if the form for verifying OTP was submitted and the request method is DELETE
        elseif ($request->input('action') === 'verify_otp' && $request->method() === 'DELETE') {
            // Logic to verify the OTP and delete the account
            // ... (Implement OTP verification and account deletion logic here)

            // Flash 'account_deleted' to the session to indicate that the account has been deleted
            return redirect()->back()->with('account_deleted', true);
        }

        // Show the initial account deletion page
        // The view path should match the actual path of your Blade file
        return view('delete-account', compact('page_title'));
    }

    public function handle(Request $request)
    {
        $page_title = 'Delete Account';

        // Check if the form for sending OTP was submitted
        if ($request->input('action') === 'send_otp') {
            // Logic to send OTP
            // ... (Implement OTP sending logic here)

            // Flash 'otp_sent' to the session to indicate that the OTP has been sent
            return redirect()->back()->with('account_deleted', true);
        }

        // Show the initial account deletion page
        // The view path should match the actual path of your Blade file
        return view('delete-account', compact('page_title'));
    }
}
