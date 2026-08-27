<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Advertisement;
use App\VitasNumber;
use App\SuccessStory;
use App\AboutVita;
use App\CommunityEvent;
use App\Testimonial;
use App\CheckEligibleUser;
use App\Governorate;
use App;

class HomeController extends Controller
{

	public function __construct(GeneralController $generalController)
	{
		$this->generalController = $generalController;
	}

	public function index($locale)
	{
		// Page Title
		$pageTitle = ($locale == 'ar') ? 'الصفحة الرئيسية' : 'Home';

		// Get Banner Section
		$banner = $this->generalController->getBanners(['footer']);
		$banner = $banner['image'];

		/* Get Website Sections */
		$sections = $this->generalController->getWebsiteSections($locale, ['home-about-us', 'vitas-in-numbers', 'success-stories', 'check-loan', 'community-events']);

		// Get Advertisements
		$advertisements = Advertisement::select([
			'image',
			'mobile_image',
			'link'
		])->orderBy('pos')->get();

		// Get All Products
		$products = Product::select([
			'id',
			'slug',
			'thumbnail',
			'title_' . $locale . ' as title',
			'text_preview_' . $locale . ' as text_preview'
		])
		->where('publish', 1)
		->orderBy('pos')->get();

		// Get All Vitas in Numbers
		$numbers = VitasNumber::select([
			'title_' . $locale . ' as title',
			'ar_value',
			'value',
			'max_value'
		])->where('pin', 1)->orderBy('pos')->get();

		// Get All Vitas in Numbers From VITAS
		// $info = [];
  //       $info['function'] = 'GetStatistics';
  //       $info['params']['AppUsername'] = env('APP_USERNAME');
  //       $info['params']['AppPassword'] = env('APP_PASSWORD');
  //       $response = parent::middleware_connect($info);
  //       if($response == false){
  //           // return parent::return_error('Failed To Connect', 456, 'Failed To Connect');
  //       }
  //       if($response['StatusCode'] != 0){
  //           // return parent::return_error($response['ActionStatus'], 457, $response['ActionStatus']);
  //       }
  //       $numbers = $response['Statistics'];

		// Get All Success Stories
		$stories = SuccessStory::select([
			'slug',
			'image',
			'title_' . $locale . ' as title',
			'text_preview_' . $locale . ' as text_preview'
		])->orderBy('pos')->get();

		// Get Vitas information
		$abouts = AboutVita::select([
			'icon',
			'value_' . $locale . ' as value',
			'title_' . $locale . ' as title'
		])->orderBy('pos')->get();

		// Get Featured Community Events
		$featuredEvent = CommunityEvent::select([
			'slug',
			'image',
			'date',
			'title_' . $locale . ' as title',
			'text_' . $locale . ' as text',
			'featured'
		])->where("featured", 1)->first();
		if($featuredEvent['date']){
			if($locale == "en"){
				$date = date("F Y", strtotime($featuredEvent['date']));
			} elseif($locale == "ar"){
				$date = $this->generalController->dateToArabic($featuredEvent['date']);
			}
			$featuredEvent['date'] = $date;
		}

		// Get All Community Events Limited by 4
		$events = CommunityEvent::select([
			'slug',
			'image',
			'date',
			'title_' . $locale . ' as title',
			'text_' . $locale . ' as text',
			'featured'
		])->where("featured", "<>", 1)->orderBy('date', 'desc')->limit(4)->get();
		foreach($events as $key => $event){
			if($event['date']){
				if($locale == "en"){
					$date = date("F Y", strtotime($event['date']));
				} elseif($locale == "ar"){
					$date = $this->generalController->dateToArabic($event['date']);
				}
				$events[$key]->date = $date;
			}
		}

		// Get Testimonials
		$testimonials = Testimonial::select([
			'name_' . $locale . ' as name',
			'position_' . $locale . ' as position',
			'text_' . $locale . ' as text'
		])->orderBy('pos')->get();

		// Get All governorates
		$governorates = Governorate::select([
			'id',
			'value_' . $locale . ' as value'
		])->orderBy('value_' . $locale)->get();

		return view('home', compact(
			'pageTitle',
			'sections',
			'advertisements',
			'products',
			'numbers',
			'stories',
			'abouts',
			'featuredEvent',
			'events',
			'testimonials',
			'governorates',
			'banner'
		));
	}

	public function checkLoan(Request $request, $locale){

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
			'title_' . $locale . ' as title',
		])->where("id", $request->type_of_loans)->where('publish', 1)->first();
		if (!$product) App::abort(404);

		$governorate = Governorate::select([
			'value_' . $locale . ' as value'
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