<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\RenewLoan;
use App\Product;
use App\Governorate;
use App;

class RenewLoanController extends Controller
{

	public function index(Request $request){

		if($access_token = parent::valid_access_token($request)) {
			if($user = parent::get_user_by_access_token($access_token)) {

				if(empty(request('loan_type'))){
					return parent::return_error("Missing Loan Type", 401, 'messages.missing_parameter');
				}
				if(empty(request('client_id'))){
					return parent::return_error("Missing Client Id", 401, 'messages.missing_parameter');
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
				if(empty(request('governorate'))){
					return parent::return_error("Missing Governorate", 401, 'messages.missing_parameter');
				}
				if(empty(request('address'))){
					return parent::return_error("Missing Address", 401, 'messages.missing_parameter');
				}

				$product = Product::select([
					'title_' . App::getLocale() . ' as title',
				])->where("id", $request->loan_type)->where('publish', 1)->first();
				if (!$product) App::abort(404);

				$governorate = Governorate::select([
					'value_' . App::getLocale() . ' as value'
				])->where("id", $request->governorate)->first();
				if (!$governorate) App::abort(404);

				$apply = new RenewLoan;
				$apply->loan_type = ucfirst(strtolower($product['title']));
				$apply->client_id = $request->client_id;
				$apply->full_name = $request->full_name;
				$apply->age = $request->age;
				$apply->phone_number = $request->phone_number;
				$apply->governorate = $governorate['value'];
				$apply->address = $request->address;
				$apply->save();

                $body = view('emails/renew-loan', [
					'loan_type' => ucfirst(strtolower($product['title'])),
					'client_id' => $request->client_id,
					'full_name' => $request->full_name,
					'age' => $request->age,
					'phone_number' => $request->phone_number,
					'governorate' => $governorate['value'],
					'address' => $request->address,
					'platform' => "App"
                ])->render();

                $this->send_email('info@vitasiraq.com','Renew a Loan Form',$body);

				return [
					'code' => 1,
					'message' => trans('messages.request_successful')
				];

			} else {
				return parent::return_error("Invalid Access Token", 401, 'messages.invalid_access_token');
			}
		} else {
			return parent::return_error("Missing Access Token", 102, 'messages.authentication_missing');
		}
	}

}
