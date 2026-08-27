<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\BranchesCategory;
use App\Branch;
use App;
use Voyager;

class BranchesController extends Controller
{
	public function branchesCategories(Request $request)
	{
		if($access_token = parent::valid_access_token($request)) {
			if($user = parent::get_user_by_access_token($access_token)){
				// Get All Bracnches Categories
				$BranchesCategories = BranchesCategory::select([
					'id',
					'marker',
					'marker_small',
					'title_' . App::getLocale() . ' as title'
				])->orderBy('pos')->get()->map(function($query){
					$query->marker = Voyager::image($query->marker);
					$query->marker_small = Voyager::image($query->marker_small);
					return $query;
				});

				return response()->json($BranchesCategories);
			}
		}

		$BranchesCategories = BranchesCategory::select([
			'id',
			'marker',
			'marker_small',
			'title_' . App::getLocale() . ' as title'
		])->where('id', 2)->orderBy('pos')->get()->map(function($query){
			$query->marker = Voyager::image($query->marker);
			$query->marker_small = Voyager::image($query->marker_small);
			return $query;
		});

		return response()->json($BranchesCategories);
	}

	public function branches(Request $request)
	{
		if($access_token = parent::valid_access_token($request)) {
			if($user = parent::get_user_by_access_token($access_token)){
				// Return Error if post parameter does not exist
				// if(empty(request('cat_id'))){
				// 	return parent::return_error("Missing Category ID", 401, 'messages.missing_parameter');
				// }
				// if(empty(request('lat'))){
				// 	return parent::return_error("Missing Lat", 401, 'messages.missing_parameter');
				// }
				// if(empty(request('lng'))){
				// 	return parent::return_error("Missing Lng", 401, 'messages.missing_parameter');
				// }
				// // Return Error if Branch does not exist
				// if(!BranchesCategory::where('id', request('cat_id'))->first()){
				// 	return parent::return_error("Invalid Category ID", 402, 'messages.invalid_parameters');
				// }
				// Get All Branches
				$branches = Branch::select([
					'id',
					'title_' . App::getLocale() . ' as title',
					'text_' . App::getLocale() . ' as text',
					'lat',
					'lng',
					'phone_number',
					'working_hours',
					'viber',
					'branches_categories_id'
				])
				->when(request('cat_id'), function($query, $key){
					return $query->where('branches_categories_id', request('cat_id'));
				})
				->get()
				->map(function($query, $key){
					if($key == 0){

						// Set full path to branch categories marker
						$query->branch_category->marker = Voyager::image($query->branch_category->marker);
						$query->branch_category->marker_small = Voyager::image($query->branch_category->marker_small);

					}

					// Set Distance
					if(request('lat') && request('lng')){
						$result = $this->distance(request('lat'), request('lng'), $query->lat, $query->lng);
						$query->distance = $result;
					}
					return $query;
				})->sortBy('distance');

				return response()->json($branches->values());
			}
		}

		$branches = Branch::select([
			'id',
			'title_' . App::getLocale() . ' as title',
			'text_' . App::getLocale() . ' as text',
			'lat',
			'lng',
			'phone_number',
			'working_hours',
			'viber',
			'branches_categories_id'
		])
		->where('branches_categories_id', 2)
		->get()
		->map(function($query, $key){
			if($key == 0){

				// Set full path to branch categories marker
				$query->branch_category->marker = Voyager::image($query->branch_category->marker);
				$query->branch_category->marker_small = Voyager::image($query->branch_category->marker_small);

			}

			// Set Distance
			if(request('lat') && request('lng')){
				$result = $this->distance(request('lat'), request('lng'), $query->lat, $query->lng);
				$query->distance = $result;
			}
			return $query;
		})->sortBy('distance');

		return response()->json($branches->values());
	}

	public function branch(Request $request)
	{
		// Return Error if post parameter does not exist
		if(empty(request('branch_id'))){
			return parent::return_error("Missing Branch ID", 401, 'messages.missing_parameter');
		}
		// Return Error if Branch does not exist
		if(!Branch::where('id', request('branch_id'))->first()){
			return parent::return_error("Invalid Branch ID", 402, 'messages.invalid_parameters');
		}
		// Get Branch Details
		$branch = Branch::select([
			'title_' . App::getLocale() . ' as title',
			'text_' . App::getLocale() . ' as text',
			'lat',
			'lng',
			'phone_number',
			'working_hours',
			'viber',
			'branches_categories_id'
		])
		->where('id', request('branch_id'))
		->get()
		->map(function($query){
			// Set full path to branche categories marker
			$query->branch_category->marker = Voyager::image($query->branch_category->marker);
			$query->branch_category->marker_small = Voyager::image($query->branch_category->marker_small);
			// Set Distance
			$result = $this->distance(32, 34, $query->lat, $query->lng);
			$query->distance = $result;
			return $query;
		});

		return response()->json($branch);
	}


	/*
	|-----------------------------------------------------------------------------------------
	| Get Distance Function between current user location and the lat, lng of the branch
	|-----------------------------------------------------------------------------------------
	|
	*/

	function distance($lat1, $lon1, $lat2, $lon2) {
		if (($lat1 == $lat2) && ($lon1 == $lon2)) {
			return 0;
		} else {
			$theta = $lon1 - $lon2;
			$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
			$dist = acos($dist);
			$dist = rad2deg($dist);
			$miles = $dist * 60 * 1.1515;
			$result = ($miles * 1.609344);

			// In KM
			return $result;
		}
	}

}
