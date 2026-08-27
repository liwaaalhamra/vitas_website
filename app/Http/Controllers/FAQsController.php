<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Faq;

class FAQsController extends Controller
{

	public function __construct(GeneralController $generalController)
	{
		$this->generalController = $generalController;
	}

	public function index($locale)
	{
		// Page Title
		$pageTitle = ($locale == 'ar') ? 'أسئلة وأجوبة' : 'FAQs';

		// Get Banner Section
		$banner = $this->generalController->getBanners(['faqs']);
		$banner = $banner['image'];

		/* Get Website Sections */
		$sections = $this->generalController->getWebsiteSections($locale, ['faqs']);

		// Get FAQs
		$faqs = Faq::select([
			'title_' . $locale . ' as title',
			'text_' . $locale . ' as text'
		])->orderBy('pos')->get();

		return view('faqs', compact(
			'pageTitle',
			'banner',
			'sections',
			'faqs'
		));
	}

	public function webview($lang)
	{
		$locale = $lang;
		// Get FAQs
		$faqs = Faq::select([
			'title_' . $locale . ' as title',
			'text_' . $locale . ' as text'
		])->orderBy('pos')->get();

		return view('webview/faqs', compact(
			'locale',
			'faqs'
		));
	}

}
