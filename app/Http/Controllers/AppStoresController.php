<?php

namespace App\Http\Controllers;

use App\Banner;
use App\SeoMetaTag;
use App\ContactInfo;

class AppStoresController extends Controller
{

	public function index()
	{
		$locale = 'en';
		$pageTitle = 'App Stores';

        // Get SEO metadata
        $seo = SeoMetaTag::select([
            'title',
            'description',
            'keywords'
        ])->first();

        // Get Banner
        $banner = Banner::select('image')->where('slug', 'footer')->first();

        // Get Footer Contact Info
        $contactFooter = [];
        $contactFooter_db = ContactInfo::select([
            'slug',
            'value_' . $locale . ' as value'
        ])->get();
        foreach ($contactFooter_db as $contact_db) $contactFooter[$contact_db['slug']] = $contact_db['value'];

		return view('app-stores', compact(
			'locale',
			'pageTitle',
			'seo',
			'banner',
			'contactFooter'
		));
	}

}
