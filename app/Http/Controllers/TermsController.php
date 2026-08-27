<?php

namespace App\Http\Controllers;

use App\WebsiteSection;

class TermsController extends Controller
{

	public function webview($lang)
	{
		$locale = $lang;
		// Get Terms & Condition and Pirvacy Policy
		$terms = WebsiteSection::select([
			'text_' . $locale . ' as text'
		])->where('slug', 'terms-and-conditions-app')->first();

		return view('webview/terms', compact(
			'locale',
			'terms'
		));
	}

}
