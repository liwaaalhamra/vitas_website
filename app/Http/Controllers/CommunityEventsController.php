<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CommunityEvent;

class CommunityEventsController extends Controller
{

	public function __construct(GeneralController $generalController)
	{
		$this->generalController = $generalController;
	}

	public function index($locale)
	{
		// Page Title
		$pageTitle = ($locale == 'ar') ? 'الفعاليات المجتمعية' : 'Community Events';

		// Get Banner Section
		$banner = $this->generalController->getBanners(['community-events']);
		$banner = $banner['image'];

		/* Get Website Sections */
		$sections = $this->generalController->getWebsiteSections($locale, ['community-events']);

		// Get Community Events
		$events = CommunityEvent::select([
			'slug',
			'title_' . $locale . ' as title',
			'date',
			'image',
			'text_' . $locale . ' as text'
		])->orderBy('date', 'desc')->get();
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

		return view('community', compact(
			'pageTitle',
			'banner',
			'sections',
			'events'
		));
	}

	public function single($locale, $slug)
	{
		// Get Single Community Event
		$event = CommunityEvent::select([
			'image',
			'title_' . $locale . ' as title',
			'date',
			'text_' . $locale . ' as text'
		])->where('slug', $slug)->first();
		if (!$event) \App::abort(404);
		if($event['date']){
			if($locale == "en"){
				$date = date("F Y", strtotime($event['date']));
			} elseif($locale == "ar"){
				$date = $this->generalController->dateToArabic($event['date']);
			}
			$event['date'] = $date;
		}

		// Page Title
		$pageTitle = ucfirst(strtolower($event['title']));

		// Set  Banner Section
		$banner = $event['image'];

		return view('community-details', compact(
			'pageTitle',
			'banner',
			'event'
		));
	}

}
