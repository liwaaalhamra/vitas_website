<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\ContactForm;

class ContactController extends Controller
{

	public function index(Request $request)
	{
		if(empty(request('first_name'))){
			return parent::return_error("Missing first name", 401, 'messages.missing_parameter');
		}
		if(empty(request('last_name'))){
			return parent::return_error("Missing last nane", 401, 'messages.missing_parameter');
		}
		if(empty(request('phone_number'))){
			return parent::return_error("Missing phone number", 401, 'messages.missing_parameter');
		}
		// if(empty(request('email_address'))){
		// 	return parent::return_error("Missing email address", 401, 'messages.missing_parameter');
		// }
		// if(empty(request('country'))){
		// 	return parent::return_error("Missing country", 401, 'messages.missing_parameter');
		// }
		// if(empty(request('governorate'))){
		// 	return parent::return_error("Missing governorate", 401, 'messages.missing_parameter');
		// }
		// if(empty(request('existing_client'))){
		// 	return parent::return_error("Missing existing client", 401, 'messages.missing_parameter');
		// }
		// if(empty(request('business_owner'))){
		// 	return parent::return_error("Missing business owner", 401, 'messages.missing_parameter');
		// }
		if(empty(request('message'))){
			return parent::return_error("Missing message", 401, 'messages.missing_parameter');
		}

		if($request['existing_client']){
			$existing_client = 'Yes';
		} else {
			$existing_client = 'No';
		}

		if($request['business_owner']){
			$business_owner = 'Yes';
		} else {
			$business_owner = 'No';
		}

		$contact_form = new ContactForm;
		$contact_form->first_name = $request->first_name;
		$contact_form->last_name = $request->last_name;
		$contact_form->mobile_number = $request->phone_number;
		$contact_form->email_address = $request->email_address;
		// $contact_form->country = $request->country;
		// $contact_form->governorate = $request->governorate;
		// $contact_form->existing_client = $existing_client;
		// $contact_form->business_owner = $business_owner;
		$contact_form->message = $request->message;
		$contact_form->save();

		return [
			'code' => 1,
			'message' => trans('messages.request_successful')
		];
	}

}
