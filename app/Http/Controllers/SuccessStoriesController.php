<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SuccessStory;

class SuccessStoriesController extends Controller
{

	public function __construct(GeneralController $generalController)
	{
		$this->generalController = $generalController;
	}

	public function single($locale, $slug)
	{
		// Get Single Product
		$story = SuccessStory::select([
			'slug',
			'image',
			'date',
			'title_' . $locale . ' as title',
			'text_' . $locale . ' as text',
			'video_link'
		])->where('slug', $slug)->first();
		if (!$story) \App::abort(404);
		if($locale == "en"){
			$date = date("F Y", strtotime($story['date']));
		} elseif($locale == "ar"){
			$date = $this->generalController->dateToArabic($story['date']);
		}
		$story['date'] = $date;

		// Page Title
		$pageTitle = ucfirst(strtolower($story['title']));

		// Set  Banner Section
		$banner = $story['image'];

		return view('story-details', compact(
			'pageTitle',
			'banner',
			'story'
		));
	}

}
