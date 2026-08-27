<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\AppBanner;
use Voyager;

class BannerController extends Controller
{

	public function index()
	{
		// Get Governorates
		$banners = AppBanner::select([
			'link',
			'image'
		])->orderBy('pos')->get()->map(function($query){
			$query->image = Voyager::image($query->image);
			return $query;
		});

		return response()->json($banners);
	}

}
