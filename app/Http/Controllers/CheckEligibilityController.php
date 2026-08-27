<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Governorate;
use App\CheckEligibleUser;
use App;
use Mail;

class CheckEligibilityController extends Controller
{

	public function __construct(GeneralController $generalController)
	{
		$this->generalController = $generalController;
	}

	public function webview($lang)
	{
		$locale = $lang;
		App::setLocale($locale);

		/* Get Website Sections */
		$sections = $this->generalController->getWebsiteSections($locale, ['check-loan']);

		// Get All Products
		$products = Product::select([
			'id',
			'title_' . $locale . ' as title',
		])
		->where('publish', 1)
		->orderBy('pos')->get();

		// Get All governorates
		$governorates = Governorate::select([
			'id',
			'value_' . $locale . ' as value'
		])->orderBy('value_' . $locale)->get();

		return view('webview/check-eligibility', compact(
			'locale',
			'sections',
			'products',
			'governorates'
		));
	}

	public function checkLoan(Request $request, $lang){

		$this->validate($request, [
			'full_name' => 'required',
			'phone' => 'required',
			'age' => 'required',
			'type_of_loans' => 'required',
			'monthly_income' => 'required',
			'monthly_expenses' => 'required',
			'governorate' => 'required',
		]);

		if($request['business_owner']){
			$business_owner = 'Yes';
		} else {
			$business_owner = 'No';
		}

		if($request['employee']){
			$employee = 'Yes';
		} else {
			$employee = 'No';
		}

		if($request['own_home']){
			$own_home = 'Yes';
		} else {
			$own_home = 'No';
		}

		if($request['disability']){
			$disability = 'Yes';
		} else {
			$disability = 'No';
		}

		if($request['guarantors']){
			$guarantors = 'Yes';
		} else {
			$guarantors = 'No';
		}

		$product = Product::select([
			'title_' . $lang . ' as title',
		])->where("id", $request->type_of_loans)->where('publish', 1)->first();
		if (!$product) App::abort(404);

		$governorate = Governorate::select([
			'value_' . $lang . ' as value'
		])->where("id", $request->governorate)->first();
		if (!$governorate) App::abort(404);

		// BUSINESS LOAN
		if($request->type_of_loans == 1){
			if($request->age < 64){
				if($request->business_owner == "on" && $request->employee == "on" || $request->business_owner == "on" && $request->employee == null){
					if(isset($request->age_business) && ($request->age_business >= 1)){
						if(($request->monthly_income * 0.7) > $request->monthly_expenses){
							if($request->guarantors == "on"){
								// if($request->governorate == 8 || $request->governorate == 6 || $request->governorate == 9 || $request->governorate == 7 || $request->governorate == 11 || $request->governorate == 10 || $request->governorate == 13 || $request->governorate == 12 || $request->governorate == 14){

								$check = new CheckEligibleUser;
								$check->full_name = $request->full_name;
								$check->phone = $request->phone;
								$check->loan_type = ucfirst(strtolower($product['title']));
								$check->age = $request->age;
								$check->business_owner = $business_owner;
								$check->employee = $employee;
								$check->business_age = $request->business_age;
								$check->monthly_income = $request->monthly_income;
								$check->monthly_expenses = $request->monthly_expenses;
								$check->guarantors = $guarantors;
								$check->governorate = $governorate['value'];
								$check->save();

                                $body = view('emails/check-eligibility', [
									'msg' => 'Eligible to apply for a loan: ',
									'loan_type' => ucfirst(strtolower($product['title'])),
									'business_owner' => $business_owner,
									'employee' => $employee,
									'full_name' => $request['full_name'],
									'age' => $request['age'],
									'phone_number' => $request['phone'],
									'governorate' => $governorate['value'],
									'business_age' => $request['business_age'],
									'monthly_income' => $request['monthly_income'],
									'monthly_expenses' => $request['monthly_expenses'],
									'guarantors' => $request['guarantors'],
									'platform' => "Website"
                                ])->render();

                                $this->send_email('info@vitasiraq.com','Website Check Eligiblity',$body);

								// Mail::send('emails/check-eligibility', [
								// 	'msg' => 'Eligible to apply for a loan: ',
								// 	'loan_type' => ucfirst(strtolower($product['title'])),
								// 	'business_owner' => $business_owner,
								// 	'employee' => $employee,
								// 	'full_name' => $request['full_name'],
								// 	'age' => $request['age'],
								// 	'phone_number' => $request['phone'],
								// 	'governorate' => $governorate['value'],
								// 	'business_age' => $request['business_age'],
								// 	'monthly_income' => $request['monthly_income'],
								// 	'monthly_expenses' => $request['monthly_expenses'],
								// 	'guarantors' => $request['guarantors'],
								// 	'platform' => "Website"
								// ], function ($message) {
								// 	$message->from('noreply@tedmob.com', 'Website Vitas');
								// 	$message->to('info@vitasiraq.com')->subject('Website Check Eligiblity');
								// });

								return redirect()->back()->with('success', trans('messages.eligible'));
								// }
							}
						}
					}
				}
			}
		}

		// HOME IMPROVEMENT LOAN
		if($request->type_of_loans == 2){
			if($request->age < 64){
				if($request->business_owner == "on" && $request->employee == "on" || $request->business_owner == null && $request->employee == "on" || $request->business_owner == "on" && $request->employee == null){
					if($request->own_home == "on"){
						if(($request->monthly_income * 0.7) > $request->monthly_expenses){
							if($request->guarantors == "on"){

								$check = new CheckEligibleUser;
								$check->full_name = $request->full_name;
								$check->phone = $request->phone;
								$check->loan_type = ucfirst(strtolower($product['title']));
								$check->age = $request->age;
								$check->business_owner = $business_owner;
								$check->employee = $employee;
								$check->own_home = $own_home;
								$check->monthly_income = $request->monthly_income;
								$check->monthly_expenses = $request->monthly_expenses;
								$check->guarantors = $guarantors;
								$check->governorate = $governorate['value'];
								$check->save();

                                $body = view('emails/check-eligibility', [
									'msg' => 'Eligible to apply for a loan: ',
									'loan_type' => ucfirst(strtolower($product['title'])),
									'business_owner' => $business_owner,
									'employee' => $employee,
									'full_name' => $request['full_name'],
									'age' => $request['age'],
									'phone_number' => $request['phone'],
									'governorate' => $governorate['value'],
									'business_age' => $request['business_age'],
									'monthly_income' => $request['monthly_income'],
									'monthly_expenses' => $request['monthly_expenses'],
									'guarantors' => $request['guarantors'],
									'own_home' => $request['own_home'],
									'platform' => "Website"
                                ])->render();

                                $this->send_email('info@vitasiraq.com','Website Check Eligiblity',$body);

								// Mail::send('emails/check-eligibility', [
								// 	'msg' => 'Eligible to apply for a loan: ',
								// 	'loan_type' => ucfirst(strtolower($product['title'])),
								// 	'business_owner' => $business_owner,
								// 	'employee' => $employee,
								// 	'full_name' => $request['full_name'],
								// 	'age' => $request['age'],
								// 	'phone_number' => $request['phone'],
								// 	'governorate' => $governorate['value'],
								// 	'business_age' => $request['business_age'],
								// 	'monthly_income' => $request['monthly_income'],
								// 	'monthly_expenses' => $request['monthly_expenses'],
								// 	'guarantors' => $request['guarantors'],
								// 	'own_home' => $request['own_home'],
								// 	'platform' => "Website"
								// ], function ($message) {
								// 	$message->from('noreply@tedmob.com', 'Website Vitas');
								// 	$message->to('info@vitasiraq.com')->subject('Website Check Eligiblity');
								// });
								return redirect()->back()->with('success', trans('messages.eligible'));
							}
						}
					}
				}
			}
		}

		// SPECIAL NEEDS LOAD
		if($request->type_of_loans == 4){
			if($request->age < 64){
				if($request->business_owner == "on" && $request->employee == null){
					if(isset($request->age_business) && ($request->age_business >= 1)){
						if($request->disability == "on"){
							if(($request->monthly_income * 0.7) > $request->monthly_expenses){
								if($request->guarantors == "on"){

									$check = new CheckEligibleUser;
									$check->full_name = $request->full_name;
									$check->phone = $request->phone;
									$check->loan_type = ucfirst(strtolower($product['title']));
									$check->age = $request->age;
									$check->business_owner = $business_owner;
									$check->employee = $employee;
									$check->business_age = $request->business_age;
									$check->disability = $disability;
									$check->monthly_income = $request->monthly_income;
									$check->monthly_expenses = $request->monthly_expenses;
									$check->guarantors = $guarantors;
									$check->governorate = $governorate['value'];
									$check->save();

                                    $body = view('emails/check-eligibility', [
										'msg' => 'Eligible to apply for a loan: ',
										'loan_type' => ucfirst(strtolower($product['title'])),
										'business_owner' => $business_owner,
										'employee' => $employee,
										'full_name' => $request['full_name'],
										'age' => $request['age'],
										'phone_number' => $request['phone'],
										'governorate' => $governorate['value'],
										'business_age' => $request['business_age'],
										'monthly_income' => $request['monthly_income'],
										'monthly_expenses' => $request['monthly_expenses'],
										'guarantors' => $request['guarantors'],
										'disability' => $request['disability'],
										'platform' => "Website"
                                    ])->render();

                                    $this->send_email('info@vitasiraq.com','Website Check Eligiblity',$body);

									// Mail::send('emails/check-eligibility', [
									// 	'msg' => 'Eligible to apply for a loan: ',
									// 	'loan_type' => ucfirst(strtolower($product['title'])),
									// 	'business_owner' => $business_owner,
									// 	'employee' => $employee,
									// 	'full_name' => $request['full_name'],
									// 	'age' => $request['age'],
									// 	'phone_number' => $request['phone'],
									// 	'governorate' => $governorate['value'],
									// 	'business_age' => $request['business_age'],
									// 	'monthly_income' => $request['monthly_income'],
									// 	'monthly_expenses' => $request['monthly_expenses'],
									// 	'guarantors' => $request['guarantors'],
									// 	'disability' => $request['disability'],
									// 	'platform' => "Website"
									// ], function ($message) {
									// 	$message->from('noreply@tedmob.com', 'Website Vitas');
									// 	$message->to('info@vitasiraq.com')->subject('Website Check Eligiblity');
									// });
									return redirect()->back()->with('success', trans('messages.eligible'));
								}
							}
						}
					}
				}
				if($request->business_owner == null && $request->employee == null || $request->business_owner == null && $request->employee == "on"){
					if(isset($request->age_business) && ($request->age_business < 1)){
						if($request->disability == "on"){
							if(($request->monthly_income * 0.7) > $request->monthly_expenses){
								if($request->guarantors == "on"){

									$check = new CheckEligibleUser;
									$check->full_name = $request->full_name;
									$check->phone = $request->phone;
									$check->loan_type = ucfirst(strtolower($product['title']));
									$check->age = $request->age;
									$check->business_owner = $business_owner;
									$check->employee = $employee;
									$check->business_age = $request->business_age;
									$check->disability = $disability;
									$check->monthly_income = $request->monthly_income;
									$check->monthly_expenses = $request->monthly_expenses;
									$check->guarantors = $guarantors;
									$check->governorate = $governorate['value'];
									$check->save();

                                    $body = view('emails/check-eligibility', [
										'msg' => 'Might be eligible to apply for a loan: ',
										'loan_type' => ucfirst(strtolower($product['title'])),
										'business_owner' => $business_owner,
										'employee' => $employee,
										'full_name' => $request['full_name'],
										'age' => $request['age'],
										'phone_number' => $request['phone'],
										'governorate' => $governorate['value'],
										'business_age' => $request['business_age'],
										'monthly_income' => $request['monthly_income'],
										'monthly_expenses' => $request['monthly_expenses'],
										'guarantors' => $request['guarantors'],
										'disability' => $request['disability'],
										'platform' => "Website"
                                    ])->render();

                                    $this->send_email('info@vitasiraq.com','Website Check Eligiblity',$body);

									// Mail::send('emails/check-eligibility', [
									// 	'msg' => 'Might be eligible to apply for a loan: ',
									// 	'loan_type' => ucfirst(strtolower($product['title'])),
									// 	'business_owner' => $business_owner,
									// 	'employee' => $employee,
									// 	'full_name' => $request['full_name'],
									// 	'age' => $request['age'],
									// 	'phone_number' => $request['phone'],
									// 	'governorate' => $governorate['value'],
									// 	'business_age' => $request['business_age'],
									// 	'monthly_income' => $request['monthly_income'],
									// 	'monthly_expenses' => $request['monthly_expenses'],
									// 	'guarantors' => $request['guarantors'],
									// 	'disability' => $request['disability'],
									// 	'platform' => "Website"
									// ], function ($message) {
									// 	$message->from('noreply@tedmob.com', 'Website Vitas');
									// 	$message->to('info@vitasiraq.com')->subject('Website Check Eligiblity');
									// });
									return redirect()->back()->with('success', trans('messages.might_be_eligible'));
								}
							}
						}
					}
				}
			}
		}

		return redirect()->back()->with('danger', trans('messages.not_eligible'));
	}

}
