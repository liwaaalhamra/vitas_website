<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\VitasNumber;
use App;

class VitasNumbersController extends Controller
{

	public function index(Request $request)
	{
		// Get All Vitas in Numbers
		$numbers = VitasNumber::select([
			'title_' . App::getLocale() . ' as title',
			'value'
		])->orderBy('pos')->get();

		return response()->json($numbers);
	}

}
