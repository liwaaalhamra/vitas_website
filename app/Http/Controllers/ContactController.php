<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ContactForm;
use Mail;
use App;

class ContactController extends Controller
{

	public function __construct(GeneralController $generalController)
	{
		$this->generalController = $generalController;
	}

	public function index($locale)
	{
		// Page Title
		$pageTitle = ($locale == 'ar') ? 'اتصل بنا' : 'Contact Us';

		// Banner Section
		$banner = $this->generalController->getBanners(['contact']);
		$banner = $banner['image'];

		/* Get Website Sections */
		$sections = $this->generalController->getWebsiteSections($locale, ['send-message', 'our-contact-info']);

		// List of countries
		$countries = array("Afghanistan", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegowina", "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo", "Congo, the Democratic Republic of the", "Cook Islands", "Costa Rica", "Cote d'Ivoire", "Croatia (Hrvatska)", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "France Metropolitan", "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard and Mc Donald Islands", "Holy See (Vatican City State)", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran (Islamic Republic of)", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan", "Lao, People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein", "Lithuania", "Luxembourg", "Macau", "Macedonia, The Former Yugoslav Republic of", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of", "Moldova, Republic of", "Monaco", "Mongolia", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Seychelles", "Sierra Leone", "Singapore", "Slovakia (Slovak Republic)", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and the South Sandwich Islands", "Spain", "Sri Lanka", "St. Helena", "St. Pierre and Miquelon", "Sudan", "Suriname", "Svalbard and Jan Mayen Islands", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan, Province of China", "Tajikistan", "Tanzania, United Republic of", "Thailand", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "United States Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Virgin Islands (British)", "Virgin Islands (U.S.)", "Wallis and Futuna Islands", "Western Sahara", "Yemen", "Yugoslavia", "Zambia", "Zimbabwe");

		return view('contact', compact(
			'pageTitle',
			'banner',
			'sections',
			'countries'
		));
	}

	public function contactUs(Request $request){

		$this->validate($request, [
			'first_name' => 'required',
			'last_name' => 'required',
			'country' => 'required',
			'place_of_residence' => 'required',
			'email_address' => 'required|email',
			'mobile_number' => 'required',
			'message' => 'required',
		]);

		if(!empty($request['website'])){
			App::abort(500);
		}

		$spam_token = $request['app'];
		$spam_token = str_replace("T","B", $spam_token);
		$spam_token = str_replace("V","X", $spam_token);
		if($spam_token != 'LDOBFKWNSBPXSY'){
			App::abort(500);
		}

		$contact_form = new ContactForm;
		$contact_form->first_name = $request->first_name;
		$contact_form->last_name = $request->last_name;
		$contact_form->country = $request->country;
		$contact_form->place_of_residence = $request->place_of_residence;
		$contact_form->email_address = $request->email_address;
		$contact_form->mobile_number = $request->mobile_number;
		$contact_form->message = $request->message;
		$contact_form->save();

        $body = view('emails/contact', [
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'country' => $request['country'],
            'place_of_residence' => $request['place_of_residence'],
            'email_address' => $request['email_address'],
            'mobile_number' => $request['mobile_number'],
            'msg' => $request['message'],
        ])->render();

        $this->send_email('info@vitasiraq.com','Website Contact Form',$body);

		// Mail::send('emails/contact', [
        //     'first_name' => $request['first_name'],
        //     'last_name' => $request['last_name'],
        //     'country' => $request['country'],
        //     'place_of_residence' => $request['place_of_residence'],
        //     'email_address' => $request['email_address'],
        //     'mobile_number' => $request['mobile_number'],
        //     'msg' => $request['message'],
        // ], function ($message) {
        //     $message->from('noreply@tedmob.com', 'Website Vitas');
        //     $message->to('info@vitasiraq.com')->subject('Website Contact Form');
        // });

		return [
			'code' => 1,
			'message' => trans('messages.request_successful')
		];

	}

}
