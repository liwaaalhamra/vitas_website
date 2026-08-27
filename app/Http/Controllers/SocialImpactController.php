<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\VitasNumber;
use App\CommunityEvent;

class SocialImpactController extends Controller
{

	public function __construct(GeneralController $generalController)
	{
		$this->generalController = $generalController;
	}

	public function index($locale)
	{
		// Page Title
		$pageTitle = ($locale == 'ar') ? 'تأثير اجتماعي' : 'Social Impact';

		// Banner Section
		$banner = $this->generalController->getBanners(['social-impact']);
		$banner = $banner['image'];

		/* Get Website Sections */
		$sections = $this->generalController->getWebsiteSections($locale, ['social-impact', 'vitas-in-numbers', 'community-events']);
		$imagesGrid = json_decode($sections['social-impact']['images']);

		// Get All Vitas in Numbers
		// $numbers_db = VitasNumber::select([
		// 	'title_' . $locale . ' as title',
		// 	'ar_value',
		// 	'value',
		// 	'max_value'
		// ])->orderBy('pos')->get();
		// $numbers = $numbers_db->chunk( round(count($numbers_db) / 2) );

		// Get All Vitas in Numbers From VITAS
		$info = [];
        $info['function'] = 'GetStatistics';
        $info['params']['AppUsername'] = env('APP_USERNAME');
        $info['params']['AppPassword'] = env('APP_PASSWORD');
        $response = parent::middleware_connect($info);
        if($response == false){
            // return parent::return_error('Failed To Connect', 456, 'Failed To Connect');
        }
        if($response['StatusCode'] != 0){
            // return parent::return_error($response['ActionStatus'], 457, $response['ActionStatus']);
        }
        $numbers = $response['Statistics'];


		// Get All Community Events Limited by 4
		$events = CommunityEvent::select([
			'slug',
			'image',
			'date',
			'title_' . $locale . ' as title',
			'text_' . $locale . ' as text',
			'featured'
		])->where("featured", "<>", 1)->orderBy('date', 'desc')->limit(4)->get();
		foreach($events as $key => $event){
			if($event['date']){
				if($locale == "en"){
					$date = date("F Y", strtotime($event['date']));
				} elseif($locale == "ar"){
					$date = $this->generalController->dateToArabic($event['date']);
				}
				$events[$key]->date = $date;
			}
		}

		return view('social-impact', compact(
			'pageTitle',
			'banner',
			'sections',
			'imagesGrid',
			'numbers',
			'events'
		));
	}

}
