<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\WebsiteSection;
use App;

class EWaletController extends Controller
{

	public function index()
	{
		// Get Governorates
		$e_wallet = WebsiteSection::select([
			'title_' . App::getLocale() . ' as title',
			'text_' . App::getLocale() . ' as text'
		])->where('slug', 'e-wallet-app')->first();

		return response()->json($e_wallet);
	}

}
