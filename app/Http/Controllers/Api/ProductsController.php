<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Product;
use App;
use Voyager;

class ProductsController extends Controller
{

	public function index(Request $request)
	{
		if($access_token = parent::valid_access_token($request)) {
			if($user = parent::get_user_by_access_token($access_token)){

				// Get All Products
				$products = Product::select([
					'id',
					'thumbnail',
					'title_' . App::getLocale() . ' as title',
					'text_preview_' . App::getLocale() . ' as text_preview'
				])
				->where('publish', 1)
				->orderBy('pos')->get()->map(function($query){
					$query->thumbnail = Voyager::image($query->thumbnail);
					return $query;
				});

				return response()->json($products);

			}
		}

		// Get All Products
		$products = Product::select([
			'id',
			'thumbnail',
			'title_' . App::getLocale() . ' as title',
			'text_preview_' . App::getLocale() . ' as text_preview'
		])
		->where('publish', 1)
		->orderBy('pos')->where('id', '!=', 3)->get()->map(function($query){
			$query->thumbnail = Voyager::image($query->thumbnail);
			return $query;
		});

		return response()->json($products);
	}

	public function single(Request $request, $id)
	{
		// Get Product by Slug
		$product = Product::select([
			'title_' . App::getLocale() . ' as title',
			'text_' . App::getLocale() . ' as text',
			'images'
		])->where('id', $id)->where('publish', 1)->firstOrFail();

		if($product['images']){
			$images = json_decode($product['images']);
			foreach ($images as $key => $image) {
				$images[$key] = Voyager::image($image);
			}
			$product['images'] = $images;
		}

		if(App::getLocale() == 'ar'){
			$product['text'] = '<div dir="rtl" style="text-align: right;">' . $product['text'] . '</div>';
		}

		return response()->json($product);
	}

}
