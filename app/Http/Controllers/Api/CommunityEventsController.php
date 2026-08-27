<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\CommunityEvent;
use App;
use Voyager;

class CommunityEventsController extends Controller
{

	public function index(Request $request)
	{
		// Get All Community Events
		$events = CommunityEvent::select([
			'image',
			'title_' . App::getLocale() . ' as title',
			'date',
			'text_' . App::getLocale() . ' as text'
		])->orderBy('date', 'desc')->get()->map(function($query){
			$query->image = Voyager::image($query->image);
			return $query;
		});

		return response()->json($events);
	}

}
