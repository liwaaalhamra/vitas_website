<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\ApplyForALoan;
use App\Governorate;
use Mail;
use App;


class ApplyLoanController extends Controller
{

	public function __construct(GeneralController $generalController)
	{
		$this->generalController = $generalController;
	}

	public function index($locale, $slug = null)
	{
		// Page Title
		$pageTitle = ($locale == 'ar') ? 'تقدم على طلب' : 'Apply for a loan';

		// Get Banner Section
		$banner = $this->generalController->getBanners(['apply-for-a-loan']);
		$banner = $banner['image'];

		/* Get Website Sections */
		$sections = $this->generalController->getWebsiteSections($locale, ['apply-for-a-loan', 'apply-for-a-loan-notice']);

		// Get All Products
		$products = Product::select([
			'id',
			'title_' . $locale . ' as title',
		])
		->where('publish', 1)
		->orderBy('pos')->get();

		if($slug){
			$selectedProduct = Product::select([
				'id',
				'title_' . $locale . ' as title',
			])->where('slug', $slug)
			->where('publish', 1)
			->first();
		} else {
			$selectedProduct = null;
		}

		// Get All governorates
		$governorates = Governorate::select([
			'id',
			'value_' . $locale . ' as value'
		])->get();

		return view('apply-for-a-loan', compact(
			'pageTitle',
			'sections',
			'banner',
			'products',
			'selectedProduct',
			'governorates'
		));
	}

	public function apply(Request $request, $locale){

		$this->validate($request, [
			'loan_type' => 'required',
			'full_name' => 'required',
			'age' => 'required',
			'phone_number' => 'required',
			// 'email_address' => 'required|email',
			'governorate' => 'required',
			'address' => 'required',
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

		$product = Product::select([
			'title_' . $locale . ' as title',
		])->where("id", $request->loan_type)->where('publish', 1)->first();
		if (!$product) App::abort(404);

		$governorate = Governorate::select([
			'value_' . $locale . ' as value'
		])->where("id", $request->governorate)->first();
		if (!$governorate) App::abort(404);

		$apply = new ApplyForALoan;
		$apply->loan_type = ucfirst(strtolower($product['title']));
		$apply->business_owner = $business_owner;
		$apply->employee = $employee;
		$apply->full_name = $request->full_name;
		$apply->age = $request->age;
		$apply->phone_number = $request->phone_number;
		$apply->email_address = $request->email_address;
		$apply->governorate = $governorate['value'];
		$apply->address = $request->address;
		$apply->platform = "Website";
		$apply->save();

        $body = view('emails.apply-loan', [
            'loan_type' => ucfirst(strtolower($product['title'])),
            'business_owner' => $business_owner,
            'employee' => $employee,
            'full_name' => $request['full_name'],
            'age' => $request['age'],
            'phone_number' => $request['phone_number'],
            'email_address' => $request['email_address'],
            'governorate' => $governorate['value'],
            'address' => $request['address'],
            'platform' => 'Website',
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
        //     'address' => $request['address'],
        //     'platform' => "Website"
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
