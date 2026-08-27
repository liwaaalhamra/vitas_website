<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\WebsiteSection;
use App\Banner;

class GeneralController extends Controller
{

	public function index($locale)
	{
		return redirect(route('home', $locale));
	}

	/*
    |--------------------------------------------------------------------------
    | Get Banners
    |--------------------------------------------------------------------------
    */

	public function getBanners($slug) {
		$banner = Banner::select([
			'image',
		])->where('slug', $slug)->first();
		return $banner;
	}

	/*
    |--------------------------------------------------------------------------
    | Get Website Sections
    |--------------------------------------------------------------------------
    */

	public function getWebsiteSections($locale, $slug) {
		$websections = [];
		$sections_db = WebsiteSection::select([
			'slug',
			'title_' . $locale . ' as title',
			'text_' . $locale . ' as text',
			'images'
		])->wherein('slug', $slug)->get()->toArray();
		foreach ($sections_db as $section_db) {
			$websections[$section_db['slug']] = [
				"title" => $section_db['title'],
				"text" => $section_db['text'],
				"images" => $section_db['images']
			];
		}
		return $websections;
	}

	/*
    |--------------------------------------------------------------------------
    | Date Functions
    |--------------------------------------------------------------------------
    */

    public static function dateToArabic($date){
    	$english_months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
    	$arabic_months = array('يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر');
    	return str_replace($english_months, $arabic_months, date("F Y", strtotime($date)));
    }

    /*
    |--------------------------------------------------------------------------
    | Get Nationalities
    |--------------------------------------------------------------------------
    */

    public static function getNationalities(){
    	$nationalities =  array("Afghan", "Albanian", "Algerian", "American", "Andorran", "Angolan", "Antiguans", "Argentinean", "Armenian", "Australian", "Austrian", "Azerbaijani", "Bahamian", "Bahraini", "Bangladeshi", "Barbadian", "Barbudans", "Batswana", "Belarusian", "Belgian", "Belizean", "Beninese", "Bhutanese", "Bolivian", "Bosnian", "Brazilian", "British", "Bruneian", "Bulgarian", "Burkinabe", "Burmese", "Burundian", "Cambodian", "Cameroonian", "Canadian", "Cape Verdean", "Central African", "Chadian", "Chilean", "Chinese", "Colombian", "Comoran", "Congolese", "Costa Rican", "Croatian", "Cuban", "Cypriot", "Czech", "Danish", "Djibouti", "Dominican", "Dutch", "East Timorese", "Ecuadorean", "Egyptian", "Emirian", "Equatorial Guinean", "Eritrean", "Estonian", "Ethiopian", "Fijian", "Filipino", "Finnish", "French", "Gabonese", "Gambian", "Georgian", "German", "Ghanaian", "Greek", "Grenadian", "Guatemalan", "Guinea-Bissauan", "Guinean", "Guyanese", "Haitian", "Herzegovinian", "Honduran", "Hungarian", "I-Kiribati", "Icelander", "Indian", "Indonesian", "Iranian", "Iraqi", "Irish", "Italian", "Ivorian", "Jamaican", "Japanese", "Jordanian", "Kazakhstani", "Kenyan", "Kittian and Nevisian", "Kuwaiti", "Kyrgyz", "Laotian", "Latvian", "Lebanese", "Liberian", "Libyan", "Liechtensteiner", "Lithuanian", "Luxembourger", "Macedonian", "Malagasy", "Malawian", "Malaysian", "Maldivan", "Malian", "Maltese", "Marshallese", "Mauritanian", "Mauritian", "Mexican", "Micronesian", "Moldovan", "Monacan", "Mongolian", "Moroccan", "Mosotho", "Motswana", "Mozambican", "Namibian", "Nauruan", "Nepalese", "New Zealander", "Nicaraguan", "Nigerian", "Nigerien", "North Korean", "Northern Irish", "Norwegian", "Omani", "Pakistani", "Palauan", "Panamanian", "Papua New Guinean", "Paraguayan", "Peruvian", "Polish", "Portuguese", "Qatari", "Romanian", "Russian", "Rwandan", "Saint Lucian", "Salvadoran", "Samoan", "San Marinese", "Sao Tomean", "Saudi", "Scottish", "Senegalese", "Serbian", "Seychellois", "Sierra Leonean", "Singaporean", "Slovakian", "Slovenian", "Solomon Islander", "Somali", "South African", "South Korean", "Spanish", "Sri Lankan", "Sudanese", "Surinamer", "Swazi", "Swedish", "Swiss", "Syrian", "Taiwanese", "Tajik", "Tanzanian", "Thai", "Togolese", "Tongan", "Trinidadian or Tobagonian", "Tunisian", "Turkish", "Tuvaluan", "Ugandan", "Ukrainian", "Uruguayan", "Uzbekistani", "Venezuelan", "Vietnamese", "Welsh", "Yemenite", "Zambian", "Zimbabwean");
    	return $nationalities;
    }

    /*
    |--------------------------------------------------------------------------
    | Get Countries
    |--------------------------------------------------------------------------
    */

    public static function getCountries($locale){
    	if($locale == 'ar'){
			$countries = array("أفغانستان", "ألبانيا", "الجزائر", "ساموا الأمريكية", "أندورا", "أنغولا", "أنغيلا", "أنتاركتيكا", "أنتيغوا وبربودا", "الأرجنتين", "أرمينيا", "أروبا ", " أستراليا ", " النمسا ", " أذربيجان ", " جزر البهاما ", " البحرين ", " بنغلاديش ", " بربادوس ", " بيلاروسيا ", " بلجيكا ", " بليز ", " بنين ", "برمودا", "بوتان", "بوليفيا", "البوسنة والهرسك", "بوتسوانا", "جزيرة بوفيت", "البرازيل", "إقليم المحيط الهندي البريطاني", "بروناي دار السلام", "بلغاريا", "بوركينا فاسو", " بوروندي ", " كمبوديا ", " الكاميرون ", " كندا ", " الرأس الأخضر ", " جزر كايمان ", " جمهورية إفريقيا الوسطى ", " تشاد ", " تشيلي ", " الصين ", " جزيرة كريسماس ", "جزر كوكوس (كيلينغ)", "كولومبيا", "جزر القمر", "الكونغو", "الكونغو , جمهورية الكونغو الديمقراطية", "جزر كوك", "كوستاريكا", "كوت ديفوار", "كرواتيا (Hrvatska) ", " كوبا ", " قبرص ", " جمهورية التشيك ", " الدنمارك ", " جيبوتي ", " دومينيكا ", " جمهورية الدومينيكان ", " تيمور الشرقية ", " إكوادور ", " مصر ", " ش سلفادور ", " غينيا الاستوائية ", " إريتريا ", " إستونيا ", " إثيوبيا ", "جزر فوكلاند (مالفيناس)", "جزر فارو", "فيجي", "فنلندا", "فرنسا", "فرنسا متروبوليتان", "غيانا الفرنسية", "بولينيزيا الفرنسية", "الأقاليم الجنوبية الفرنسية", "غابون", "غامبيا", "جورجيا", "ألمانيا", "غانا", "جبل طارق", "اليونان", "جرينلاند", "غرينادا", "غوادلوب", "غوام", "غواتيمالا", "غواتيمالا", "غينيا", "غينيا بيساو", "غيانا", "هايتي", "جزر هيرد وماكدونالد ", " الكرسي الرسولي (دولة مدينة الفاتيكان) ", " هندوراس ", " هونج كونج ", " هنغاريا ", " أيسلندا ", " الهند ", "إندونيسيا", "إيران (جمهورية - الإسلامية)", "العراق", "أيرلندا", "إيطاليا", "جامايكا", "اليابان", "الأردن", "كازاخستان", "كينيا", "كيريباتي", "كوريا , جمهورية كوريا الديمقراطية الشعبية", "كوريا , جمهورية", "الكويت", "قيرغيزستان", "لاو , جمهورية كوريا الديمقراطية الشعبية", "لاتفيا", "لبنان", "ليسوتو", "ليبيريا ", " الجماهيرية العربية الليبية ", " ليختنشتاين ", " ليتوانيا ", " لوكسمبورغ ", " ماكاو ", " مقدونيا , جمهورية يوغوسلافيا السابقة ", " مدغشقر ", " ملاوي ", " ماليزيا ", " جزر المالديف ", "مالي", "مالطا", "جزر مارشال", "مارتينيك ue ", " موريتانيا ", " موريشيوس ", " مايوت ", " المكسيك ", " ميكرونيزيا , الولايات المتحدة ", " مولدوفا , جمهورية ", " موناكو ", " منغوليا ", " مونتسيرات ", " المغرب ", "موزمبيق", "ميانمار", "ناميبيا", "ناورو", "نيبال", "هولندا", "جزر الأنتيل الهولندية", "كاليدونيا الجديدة", "نيوزيلندا", "نيكاراغوا", "النيجر", "نيجيريا", "نيوي", "جزيرة نورفولك", "جزر ماريانا الشمالية", "النرويج", "عمان", "باكستان", "بالاو", "بنما", "بابوا غينيا الجديدة", "باراجواي", "بيرو", "الفلبين", "بيتكيرن", "بولندا", "البرتغال", "بورتوريكو", "قطر", "ريونيون", "رومانيا", "الاتحاد الروسي", "رواندا", "سانت كيتس ونيفيس", " سانت لوسيا ", " سانت فنسنت وجزر غرينادين ", " ساموا ", " سان مارينو ", " سان تومي وبرينسيبي ", " المملكة العربية السعودية ", " السنغال ", " سيشيل ", " سيراليون ", " سنغافورة ", "سلوفاكيا (جمهورية سلوفاكيا)", "سلوفينيا", "جزر سليمان", "الصومال", "جنوب إفريقيا", "جورجيا الجنوبية وجزر ساندويتش الجنوبية", "إسبانيا", "سريلانكا", "سانت هيلانة ", " سانت بيير وميكلون ", " السودان ", " سورينام ", " جزر سفالبارد وجان ماين ", " سوازيلاند ", " السويد ", " سويسرا ", " الجمهورية العربية السورية ", " تايوان , مقاطعة الصين ", " طاجيكستان ", "تنزانيا , جمهورية المتحدة ", " تايلاند ", " توغو ", " توكيلاو ", " تونغا ", " ترينيداد وتوباغو ", " تونس ", " تركيا ", " تركمانستان ", " جزر تركس وكايكوس ", "توفالو", "أوغندا", "أوكرانيا", "الإمارات العربية المتحدة", "المملكة المتحدة", "الولايات المتحدة", "جزر الولايات المتحدة البعيدة الصغيرة", "أوروغواي", "أوزبكستان", "فانواتو", "فنزويلا ", " فيتنام ", " جزر فيرجن (البريطانية) ", " جزر فيرجن (الولايات المتحدة) ", " جزر واليس وفوتونا ", " الصحراء الغربية ", " اليمن ", " يوغوسلافيا ", " زامبيا ", " زيمبابوي ");
		} else {
			$countries = array("Afghanistan", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegowina", "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo", "Congo, the Democratic Republic of the", "Cook Islands", "Costa Rica", "Cote d'Ivoire", "Croatia (Hrvatska)", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "France Metropolitan", "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard and Mc Donald Islands", "Holy See (Vatican City State)", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan", "Lao, People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein", "Lithuania", "Luxembourg", "Macau", "Macedonia, The Former Yugoslav Republic of", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of", "Moldova, Republic of", "Monaco", "Mongolia", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Seychelles", "Sierra Leone", "Singapore", "Slovakia (Slovak Republic)", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and the South Sandwich Islands", "Spain", "Sri Lanka", "St. Helena", "St. Pierre and Miquelon", "Sudan", "Suriname", "Svalbard and Jan Mayen Islands", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan, Province of China", "Tajikistan", "Tanzania, United Republic of", "Thailand", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "United States Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Virgin Islands (British)", "Virgin Islands (U.S.)", "Wallis and Futuna Islands", "Western Sahara", "Yemen", "Yugoslavia", "Zambia", "Zimbabwe");
		}
    	return $countries;
    }

}
