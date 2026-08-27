<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\HistoryEvent;

class AboutController extends Controller
{

	public function __construct(GeneralController $generalController)
	{
		$this->generalController = $generalController;
	}

	public function index($locale)
	{
		// Page Title
		$pageTitle = ($locale == 'ar') ? 'من نحن' : 'About Us';

		// Get Banner Section
		$banner = $this->generalController->getBanners(['about']);
		$banner = $banner['image'];

		/* Get Website Sections */
		$sections = $this->generalController->getWebsiteSections($locale, ['about-us', 'our-history']);
		$imagesGrid = json_decode($sections['about-us']['images']);

		// Get Our History Section
		$history_events = HistoryEvent::select([
			'image',
			'date',
			'title_' . $locale . ' as title',
			'text_' . $locale . ' as text',
		])->orderBy('date', 'desc')->get();
		foreach($history_events as $key => $event){
			if($locale == "en"){
				$date = date("F Y", strtotime($event['date']));
			} elseif($locale == "ar"){
				$date = $this->generalController->dateToArabic($event['date']);
			}
			$history_events[$key]->date = $date;
		}

		return view('about', compact(
			'pageTitle',
			'sections',
			'banner',
			'imagesGrid',
			'history_events'
		));
	}

}
