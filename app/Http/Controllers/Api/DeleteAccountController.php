<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DeleteAccountController extends Controller
{
	/*
	* DELETE ACCOUNT REQUEST
	* Accepts the user's email and acknowledges the request — nothing is
	* deleted here, the support team reviews the request and gets back to
	* the user (same behaviour as the website's /{locale}/delete-account page).
	*/
	public function index(Request $request)
	{
        return parent::return_json(['message' => trans('messages.delete_account_request_received')]);
	}
}
