<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\WebsiteSection;
use App\ContactInfo;
use App;
use Storage;

class AboutUsController extends Controller
{

	public function index(Request $request)
	{
		// Get About Us Sections
		$about = WebsiteSection::select([
			'images',
			'text_' . App::getLocale() . ' as text'
		])->where('slug', 'about-us-app')->first();

		$image = json_decode($about['images'])[0];
		$image_path = url('/') . Storage::url($image);

		// Get Social Media Links
        $links = [];
        $links_db = ContactInfo::select([
            'slug',
            'value_' . App::getLocale() . ' as value'
        ])
        ->where('slug', 'facebook')
        ->orwhere('slug', 'instagram')
        ->orwhere('slug', 'youtube')
        ->orwhere('slug', 'viber')
        ->orwhere('slug', 'messenger')
        ->orwhere('slug', 'call-center-number')
        ->get();
        foreach ($links_db as $link_db) $links[$link_db['slug']] = $link_db['value'];

        $array = [
        	'image' => $image_path,
        	'text' => $about['text'],
        	'facebook' => $links['facebook'],
        	'instagram' => $links['instagram'],
        	'youtube' => $links['youtube'],
            'viber' => $links['viber'],
            'messenger' => $links['messenger'],
            'call-center-number' => $links['call-center-number']
        ];

        return response()->json($array);
	}

}
