<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Governorate;
use App;

class GovernorateController extends Controller
{

	public function index()
	{
		// Get Governorates
		$governorates = Governorate::select([
			'id',
			'value_' . App::getLocale() . ' as value'
		])->get();

		return response()->json($governorates);
	}

}
