<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\ReferFriend;
use App\Product;
use App\Governorate;
use App\Models\Api\AppUsers;
use App\Models\Api\AppUsersPoint;
use App;
use Mail;

class ReferFriendController extends Controller
{

	public function index(Request $request){
		if($access_token = parent::valid_access_token($request)) {
			if($user = parent::get_user_by_access_token($access_token)){

				if(empty(request('loan_type'))){
					return parent::return_error("Missing Loan Type", 401, 'messages.missing_parameter');
				}
				if(empty(request('full_name'))){
					return parent::return_error("Missing Full Name", 401, 'messages.missing_parameter');
				}
				// if(empty(request('age'))){
				// 	return parent::return_error("Missing Age", 401, 'messages.missing_parameter');
				// }
				if(empty(request('phone_number'))){
					return parent::return_error("Missing Phone Number§", 401, 'messages.missing_parameter');
				}
				// if(empty(request('email_address'))){
				// 	return parent::return_error("Missing Email Address", 401, 'messages.missing_parameter');
				// }
				if(empty(request('governorate'))){
					return parent::return_error("Missing Governorate", 401, 'messages.missing_parameter');
				}
				// if(empty(request('address'))){
				// 	return parent::return_error("Missing Address", 401, 'messages.missing_parameter');
				// }

				// if($request['business_owner']){
				// 	$business_owner = 'Yes';
				// } else {
				// 	$business_owner = 'No';
				// }

				$product = Product::select([
					'title_' . App::getLocale() . ' as title',
				])->where("id", $request->loan_type)->where('publish', 1)->first();
				if (!$product) App::abort(404);

				$governorate = Governorate::select([
					'value_' . App::getLocale() . ' as value'
				])->where("id", $request->governorate)->first();
				if (!$governorate) App::abort(404);

				$refer = new ReferFriend;
				$refer->user_id = $user->employee_id;
				$refer->loan_type = ucfirst(strtolower($product['title']));
				// $refer->business_owner = $business_owner;
				$refer->full_name = $request->full_name;
				// $refer->age = $request->age;
				$refer->phone_number = $request->phone_number;
				// $refer->email_address = $request->email_address;
				$refer->governorate = $governorate['value'];
				// $refer->address = $request->address;
				$refer->save();

				if(!$user_points = AppUsersPoint::where([['user_id', $user->employee_id], ['meta_name', 'refere_a_friend']])->first()){
					$user_points = new AppUsersPoint;
					$user_points->user_id = $user->employee_id;
					$user_points->meta_name = "refere_a_friend";
					$user_points->meta_value = $refer->email_address;
					$user_points->points = 250;
					$user_points->description = "Refere a friend: Not Added";
					$user_points->save();
				}

                $body = view('emails/refer-a-friend', [
					'user_name' => isset($user->english_name)? $user->english_name : $user->first_name,
					'loan_type' => ucfirst(strtolower($product['title'])),
					'full_name' => $request->full_name,
					'phone_number' => $request->phone_number,
					'governorate' => $governorate['value'],
					'platform' => "App"
                ])->render();

                $this->send_email('info@vitasiraq.com','App Refer A Friend',$body);

				// Mail::send('emails/refer-a-friend', [
				// 	'user_name' => isset($user->english_name)? $user->english_name : $user->first_name,
				// 	'loan_type' => ucfirst(strtolower($product['title'])),
				// 	'full_name' => $request->full_name,
				// 	'phone_number' => $request->phone_number,
				// 	'governorate' => $governorate['value'],
				// 	'platform' => "App"
				// ], function ($message) {
				// 	$message->from('noreply@tedmob.com', 'App Vitas');
				// 	$message->to('info@vitasiraq.com')->subject('App Refer A Friend');
				// });
				return [
					'code' => 1,
					'message' => trans('messages.request_successful')
				];
			}

		}
	}
}
