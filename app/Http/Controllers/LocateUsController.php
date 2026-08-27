<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Branch;

class LocateUsController extends Controller
{

	public function __construct(GeneralController $generalController)
	{
		$this->generalController = $generalController;
	}

	public function index($locale)
	{
		// Page Title
		$pageTitle = ($locale == 'ar') ? 'مواقعنا' : 'Locate Us';

		// Get Banner Section
		$banner = $this->generalController->getBanners(['locate-us']);
		$banner = $banner['image'];

		/* Get Website Sections */
		$sections = $this->generalController->getWebsiteSections($locale, ['locate-us']);

		// Get Branches
		$branches = Branch::select([
			'id',
			'title_' . $locale . ' as title',
			'text_' . $locale . ' as text',
			'lat',
			'lng'
		])->where('branches_categories_id', 2)->orderBy('pos')->get();

		$latlng = [];
        foreach ($branches as $key => $branch) {
            $latlng[$key] = [
                'id' => $branch['id'],
                'lat' => $branch['lat'],
                'lng' => $branch['lng']
            ];
        }

		return view('locate', compact(
			'pageTitle',
			'banner',
			'sections',
			'branches',
			'latlng'
		));
	}

}
