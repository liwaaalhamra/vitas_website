<?php

namespace App\Http\Controllers;

use App\WebsiteSection;

class HowToCollect extends Controller
{

	public function webview($lang)
	{
		$locale = $lang;
		// Get Terms & Condition and Pirvacy Policy
		$howtocollect = WebsiteSection::select([
			'text_' . $locale . ' as text'
		])->where('slug', 'how-to-collect-app')->first();

		return view('webview/how-to-collect', compact(
			'locale',
			'howtocollect'
		));
	}

}
