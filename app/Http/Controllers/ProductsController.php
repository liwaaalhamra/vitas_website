<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;

class ProductsController extends Controller
{

	public function __construct(GeneralController $generalController)
	{
		$this->generalController = $generalController;
	}

	public function index($locale)
	{
		// Page Title
		$pageTitle = ($locale == 'ar') ? 'منتجات' : 'Products';

		// Get  Banner Section
		$banner = $this->generalController->getBanners(['products']);
		$banner = $banner['image'];

		// Get All Products
		$products = Product::select([
			'slug',
			'title_' . $locale . ' as title',
			'subtitle_' . $locale . ' as subtitle',
			'thumbnail',
			'text_preview_' . $locale . ' as text_preview'
		])
		->where('publish', 1)
		->orderBy('pos')->get();

		return view('products', compact(
			'pageTitle',
			'banner',
			'products'
		));
	}

	public function single($locale, $slug)
	{
		// Get Single Product
		$product = Product::select([
			'slug',
			'title_' . $locale . ' as title',
			'text_' . $locale . ' as text',
			'thumbnail',
			'images'
		])->where('slug', $slug)
		->where('publish', 1)
		->first();
		if (!$product) App::abort(404);

		// Page Title
		$pageTitle = ucfirst(strtolower($product['title']));

		// Set  Banner Section
		$banner = $product['thumbnail'];

		// Set Images Grid
		if(isset($product['images'])){
			$imagesGrid = json_decode($product['images']);
		} else {
			$imagesGrid = null;
		}

		return view('product-details', compact(
			'pageTitle',
			'banner',
			'product',
			'imagesGrid'
		));
	}

}
