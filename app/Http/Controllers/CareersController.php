<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Career;
use App\Vacancy;
use App\AllPosition;
use App\EducationLevel;
use App\YearsOfExperience;
use App\Governorate;
use Storage;
use Mail;
use App;

class CareersController extends Controller
{

	public function __construct(GeneralController $generalController)
	{
		$this->generalController = $generalController;
	}

	public function index($locale)
	{
		// Page Title
		$pageTitle = ($locale == 'ar') ? 'وظائف' : 'Careers';

		// Get Banner Section
		$banner = $this->generalController->getBanners(['careers']);
		$banner = $banner['image'];

		/* Get Website Sections */
		$sections = $this->generalController->getWebsiteSections($locale, ['current-vacancies', 'future-vacancies']);

		// Get Careers
		$careers = Career::select([
			'slug',
			'title_' . $locale . ' as title',
			'subtitle_' . $locale . ' as subtitle',
			'text_' . $locale . ' as text'
		])->orderBy('pos')->get();

		return view('careers', compact(
			'pageTitle',
			'banner',
			'sections',
			'careers'
		));
	}

	public function singleCareer($locale, $slug)
	{
		// Page Title
		$pageTitle = ($locale == 'ar') ? 'التقدم لشغل وظيفة' : 'Apply for a vacancy';

		// Get Banner Sections
		$banner = $this->generalController->getBanners(['apply-for-a-vacancy']);
		$banner = $banner['image'];

		/* Get Website Sections */
		$sections = $this->generalController->getWebsiteSections($locale, ['career-vacancies', 'future-vacancies']);

		// Get Selected Careers
		$selectedCareer = Career::select([
			'title_' . $locale . ' as title'
		])->where('slug', $slug)->first();
		if (!$selectedCareer) App::abort(404);

		// List of nationalities and countries
		$nationalities = $this->generalController->getNationalities();
		$countries = $this->generalController->getCountries($locale);

		// Get All governorates
		$governorates = Governorate::select([
			'id',
			'value_' . $locale . ' as value'
		])->get();

		// Get Levels of educations
		$levels = EducationLevel::select([
			'id',
			'value_' . $locale . ' as value'
		])->get();

		// Get Years of Experience
		$years = YearsOfExperience::select([
			'id',
			'value_' . $locale . ' as value'
		])->get();

		return view('apply-for-a-vacancy', compact(
			'pageTitle',
			'banner',
			'sections',
			'selectedCareer',
			'nationalities',
			'countries',
			'levels',
			'years',
			'governorates'
		));
	}

	public function apply(Request $request, $locale){

		$this->validate($request, [
			'first_name' => 'required',
			'last_name' => 'required',
			'date_of_birth' => 'required',
			'gender' => 'required',
			'nationality' => 'required',
			// 'governorate' => 'required',
			'country' => 'required',
			'mobile_number' => 'required',
			// 'email_address' => 'required|email',
			'position' => 'required',
			'level_of_education' => 'required',
			'message' => 'required',
			'cv' => 'required|max:10000|mimes:doc,docx,pdf'
		]);

		if(isset($request->governorate) && $request->governorate){
			$governorate = Governorate::select([
				'value_' . $locale . ' as value'
			])->where("id", $request->governorate)->first()['value'];
			if (!$governorate) App::abort(404);
		} else {
			$governorate = null;
		}

		$educationLevel = EducationLevel::select([
			'value_' . $locale . ' as value'
		])->where("id", $request->level_of_education)->first();
		if (!$educationLevel) App::abort(404);

		$years_of_experience = YearsOfExperience::select([
			'value_' . $locale . ' as value'
		])->where("id", $request->years_of_experience)->first();
		if (!$years_of_experience) App::abort(404);

		$file = $request->cv;
		$fileName = basename($file->getClientOriginalName(), '.'.$file->getClientOriginalExtension());
		$fileName = str_replace(' ', '-', $fileName);
		$fileExtension = $file->getClientOriginalExtension();

		$originalName = $fileName . "-" . rand() . "." . $fileExtension;
		$cv = $file->storeAs('public/cv', $originalName);
		$cv_path = url('/') . Storage::url($cv);

		$vacancy = new Vacancy;
		$vacancy->first_name = $request->first_name;
		$vacancy->last_name = $request->last_name;
		$vacancy->date_of_birth = $request->date_of_birth;
		$vacancy->gender = $request->gender;
		$vacancy->nationality = $request->nationality;
		$vacancy->place_of_residence = $governorate;
		$vacancy->country = $request->country;
		$vacancy->mobile_number = $request->mobile_number;
		$vacancy->email_address = $request->email_address;
		$vacancy->position = $request->position;
		$vacancy->level_of_education = $educationLevel['value'];
		$vacancy->major = $request->major;
		$vacancy->last_job_title = $request->last_job_title;
		$vacancy->years_of_experience = $years_of_experience['value'];
		$vacancy->field_of_experience = $request->field_of_experience;
		$vacancy->message = $request->message;
		$vacancy->cv = $cv_path;
		$vacancy->open_vacancy = 'Yes';
		$vacancy->save();

        $body = view('emails/vacancy', [
			'first_name' => $request['first_name'],
			'last_name' => $request['last_name'],
			'date_of_birth' => $request['date_of_birth'],
			'gender' => $request['gender'],
			'nationality' => $request['nationality'],
			'place_of_residence' => $governorate,
			'country' => $request['country'],
			'mobile_number' => $request['mobile_number'],
			'email_address' => $request['email_address'],
			'position' => $request['position'],
			'level_of_education' => $educationLevel['value'],
			'major' => $request['major'],
			'last_job_title' => $request['last_job_title'],
			'years_of_experience' => $years_of_experience['value'],
			'field_of_experience' => $request['field_of_experience'],
			'msg' => $request['message'],
			'cv' => $cv_path,
			'open_vacancy' => 'Yes',
        ])->render();

        $this->send_email('jobs@vitasiraq.com','Website Apply For Vacancy Form',$body);


		// Mail::send('emails/vacancy', [
		// 	'first_name' => $request['first_name'],
		// 	'last_name' => $request['last_name'],
		// 	'date_of_birth' => $request['date_of_birth'],
		// 	'gender' => $request['gender'],
		// 	'nationality' => $request['nationality'],
		// 	'place_of_residence' => $governorate,
		// 	'country' => $request['country'],
		// 	'mobile_number' => $request['mobile_number'],
		// 	'email_address' => $request['email_address'],
		// 	'position' => $request['position'],
		// 	'level_of_education' => $educationLevel['value'],
		// 	'major' => $request['major'],
		// 	'last_job_title' => $request['last_job_title'],
		// 	'years_of_experience' => $years_of_experience['value'],
		// 	'field_of_experience' => $request['field_of_experience'],
		// 	'msg' => $request['message'],
		// 	'cv' => $cv_path,
		// 	'open_vacancy' => 'Yes',
		// ], function ($message) {
		// 	$message->from('noreply@tedmob.com', 'Website Vitas');
		// 	$message->to('jobs@vitasiraq.com')->subject('Website Apply For Vacancy Form');
		// });

		return [
			'code' => 1,
			'message' => trans('messages.request_successful')
		];

	}

	public function futureVacancies($locale)
	{
		// Page Title
		$pageTitle = ($locale == 'ar') ? 'التقدم بطلب للحصول على وظيفة في المستقبل' : 'Apply for future vacancies';

		// Get Banner Section
		$banner = $this->generalController->getBanners(['apply-for-future-vacancies']);
		$banner = $banner['image'];

		// Get All Positions
		$positions = AllPosition::select([
			'id',
			'value_' . $locale . ' as value'
		])->get();

		/* Get Website Sections */
		$sections = $this->generalController->getWebsiteSections($locale, ['future-vacancies']);

		// List of countries
		$nationalities = $this->generalController->getNationalities();
		$countries = $this->generalController->getCountries($locale);

		// Get All governorates
		$governorates = Governorate::select([
			'id',
			'value_' . $locale . ' as value'
		])->get();

		// Get Levels of educations
		$levels = EducationLevel::select([
			'id',
			'value_' . $locale . ' as value'
		])->get();

		// Get Years of Experience
		$years = YearsOfExperience::select([
			'id',
			'value_' . $locale . ' as value'
		])->get();

		return view('apply-for-future-vacancies', compact(
			'pageTitle',
			'banner',
			'sections',
			'positions',
			'nationalities',
			'countries',
			'levels',
			'years',
			'governorates'
		));
	}

	public function applyFuture(Request $request, $locale){

		$this->validate($request, [
			'first_name' => 'required',
			'last_name' => 'required',
			'date_of_birth' => 'required',
			'gender' => 'required',
			'nationality' => 'required',
			// 'governorate' => 'required',
			'country' => 'required',
			'mobile_number' => 'required',
			// 'email_address' => 'required|email',
			'position' => 'required',
			'level_of_education' => 'required',
			'message' => 'required',
			'cv' => 'required|max:10000|mimes:doc,docx,pdf'
		]);

		if(isset($request->governorate) && $request->governorate){
			$governorate = Governorate::select([
				'value_' . $locale . ' as value'
			])->where("id", $request->governorate)->first()['value'];
			if (!$governorate) App::abort(404);
		} else {
			$governorate = null;
		}

		$position = AllPosition::select([
			'value_' . $locale . ' as value'
		])->where("id", $request->position)->first();
		if (!$position) App::abort(404);

		$educationLevel = EducationLevel::select([
			'value_' . $locale . ' as value'
		])->where("id", $request->level_of_education)->first();
		if (!$educationLevel) App::abort(404);

		$years_of_experience = YearsOfExperience::select([
			'value_' . $locale . ' as value'
		])->where("id", $request->years_of_experience)->first();
		if (!$years_of_experience) App::abort(404);

		$file = $request->cv;
		$fileName = basename($file->getClientOriginalName(), '.'.$file->getClientOriginalExtension());
		$fileName = str_replace(' ', '-', $fileName);
		$fileExtension = $file->getClientOriginalExtension();

		$originalName = $fileName . "-" . rand() . "." . $fileExtension;
		$cv = $file->storeAs('public/cv', $originalName);
		$cv_path = url('/') . Storage::url($cv);

		$vacancy = new Vacancy;
		$vacancy->first_name = $request->first_name;
		$vacancy->last_name = $request->last_name;
		$vacancy->date_of_birth = $request->date_of_birth;
		$vacancy->gender = $request->gender;
		$vacancy->nationality = $request->nationality;
		$vacancy->place_of_residence = $governorate;
		$vacancy->country = $request->country;
		$vacancy->mobile_number = $request->mobile_number;
		$vacancy->email_address = $request->email_address;
		$vacancy->position = $position['value'];
		$vacancy->level_of_education = $educationLevel['value'];
		$vacancy->major = $request->major;
		$vacancy->last_job_title = $request->last_job_title;
		$vacancy->years_of_experience = $years_of_experience['value'];
		$vacancy->field_of_experience = $request->field_of_experience;
		$vacancy->message = $request->message;
		$vacancy->cv = $cv_path;
		$vacancy->open_vacancy = 'No';
		$vacancy->save();

        $body = view('emails/vacancy', [
			'first_name' => $request['first_name'],
			'last_name' => $request['last_name'],
			'date_of_birth' => $request['date_of_birth'],
			'gender' => $request['gender'],
			'nationality' => $request['nationality'],
			'place_of_residence' => $governorate,
			'country' => $request['country'],
			'mobile_number' => $request['mobile_number'],
			'email_address' => $request['email_address'],
			'position' => $position['value'],
			'level_of_education' => $educationLevel['value'],
			'major' => $request['major'],
			'last_job_title' => $request['last_job_title'],
			'years_of_experience' => $years_of_experience['value'],
			'field_of_experience' => $request['field_of_experience'],
			'msg' => $request['message'],
			'cv' => $cv_path,
			'open_vacancy' => 'No',
        ])->render();

        $this->send_email('jobs@vitasiraq.com','Website Apply For Future Vacancy Form',$body);

		// Mail::send('emails/vacancy', [
		// 	'first_name' => $request['first_name'],
		// 	'last_name' => $request['last_name'],
		// 	'date_of_birth' => $request['date_of_birth'],
		// 	'gender' => $request['gender'],
		// 	'nationality' => $request['nationality'],
		// 	'place_of_residence' => $governorate,
		// 	'country' => $request['country'],
		// 	'mobile_number' => $request['mobile_number'],
		// 	'email_address' => $request['email_address'],
		// 	'position' => $position['value'],
		// 	'level_of_education' => $educationLevel['value'],
		// 	'major' => $request['major'],
		// 	'last_job_title' => $request['last_job_title'],
		// 	'years_of_experience' => $years_of_experience['value'],
		// 	'field_of_experience' => $request['field_of_experience'],
		// 	'msg' => $request['message'],
		// 	'cv' => $cv_path,
		// 	'open_vacancy' => 'No',
		// ], function ($message) {
		// 	$message->from('noreply@tedmob.com', 'Website Vitas');
		// 	$message->to('jobs@vitasiraq.com')->subject('Website Apply For Future Vacancy Form');
		// });

		return [
			'code' => 1,
			'message' => trans('messages.request_successful')
		];

	}

}
