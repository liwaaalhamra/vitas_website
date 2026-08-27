<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\ApplyForALoan;
use App\Product;
use App\Governorate;
use Mail;
use App;

class ApplyLoanController extends Controller
{

	public function index(Request $request){

		if(empty(request('loan_type'))){
			return parent::return_error("Missing Loan Type", 401, 'messages.missing_parameter');
		}
		if(empty(request('full_name'))){
			return parent::return_error("Missing Full Name", 401, 'messages.missing_parameter');
		}
		if(empty(request('age'))){
			return parent::return_error("Missing Age", 401, 'messages.missing_parameter');
		}
		if(empty(request('phone_number'))){
			return parent::return_error("Missing Phone Number", 401, 'messages.missing_parameter');
		}
		// if(empty(request('email_address'))){
		// 	return parent::return_error("Missing Email Address", 401, 'messages.missing_parameter');
		// }
		if(empty(request('governorate'))){
			return parent::return_error("Missing Governorate", 401, 'messages.missing_parameter');
		}
		if(empty(request('address'))){
			return parent::return_error("Missing Address", 401, 'messages.missing_parameter');
		}

		if(isset($request['business_owner']) && $request['business_owner'] == 1){
			$business_owner = 'Yes';
		} elseif(isset($request['business_owner']) && $request['business_owner'] == 0) {
			$business_owner = 'No';
		} else {
			return parent::return_error("Invalid Business Owner", 401, 'messages.invalid_parameters');
		}

		if(isset($request['employee']) && $request['employee'] == 1){
			$employee = 'Yes';
		} elseif(isset($request['employee']) && $request['employee'] == 0) {
			$employee = 'No';
		} else {
			return parent::return_error("Invalid Employee", 401, 'messages.invalid_parameters');
		}

		if(isset($request['home_improvement']) && $request['home_improvement'] == 1){
			$home_improvement = 'Yes';
		} elseif(isset($request['home_improvement']) && $request['home_improvement'] == 0) {
			$home_improvement = 'No';
		} else {
			return parent::return_error("Invalid Home Improvement", 401, 'messages.invalid_parameters');
		}

		$product = Product::select([
			'title_' . App::getLocale() . ' as title',
		])->where("id", $request->loan_type)->where('publish', 1)->first();
		if (!$product) App::abort(404);

		$governorate = Governorate::select([
			'value_' . App::getLocale() . ' as value'
		])->where("id", $request->governorate)->first();
		if (!$governorate) App::abort(404);

		$apply = new ApplyForALoan;
		$apply->loan_type = ucfirst(strtolower($product['title']));
		$apply->business_owner = $business_owner;
		$apply->employee = $employee;
		$apply->home_improvement = $home_improvement;
		$apply->full_name = $request->full_name;
		$apply->age = $request->age;
		$apply->phone_number = $request->phone_number;
		$apply->email_address = $request->email_address;
		$apply->governorate = $governorate['value'];
		$apply->address = $request->address;
		$apply->platform = "Applications";
		$apply->save();

        $body = view('emails/apply-loan', [
            'loan_type' => ucfirst(strtolower($product['title'])),
            'business_owner' => $business_owner,
            'employee' => $employee,
            'full_name' => $request['full_name'],
            'age' => $request['age'],
            'phone_number' => $request['phone_number'],
            'email_address' => $request['email_address'],
            'governorate' => $governorate['value'],
            'home_improvement' => $request['home_improvement'],
            'address' => $request['address'],
            'platform' => "Applications"
        ])->render();

        $this->send_email('info@vitasiraq.com','Website Apply For a Loan Form',$body);

		// Mail::send('emails/apply-loan', [
        //     'loan_type' => ucfirst(strtolower($product['title'])),
        //     'business_owner' => $business_owner,
        //     'employee' => $employee,
        //     'full_name' => $request['full_name'],
        //     'age' => $request['age'],
        //     'phone_number' => $request['phone_number'],
        //     'email_address' => $request['email_address'],
        //     'governorate' => $governorate['value'],
        //     'home_improvement' => $request['home_improvement'],
        //     'address' => $request['address'],
        //     'platform' => "Applications"
        // ], function ($message) {
        //     $message->from('noreply@tedmob.com', 'Website Vitas');
        //     $message->to('info@vitasiraq.com')->subject('Website Apply For a Loan Form');
        // });

		return [
			'code' => 1,
			'message' => trans('messages.request_successful')
		];
	}

}
