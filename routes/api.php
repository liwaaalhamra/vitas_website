<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
	return $request->user();
});

/*
|--------------------------------------------------------------------------
| Mobile app's routes
|--------------------------------------------------------------------------
|
*/

Route::group(['middleware' => 'locale', 'namespace' => 'Api'], function () {

	Route::group(['prefix' => 'products'],function () {
		Route::get('/', 'ProductsController@index');
		Route::get('/{id}', 'ProductsController@single');
	});

	Route::get('/community-events', 'CommunityEventsController@index');
	Route::get('/vitas-in-numbers', 'VitasNumbersController@index');

	Route::group(['prefix' => 'branches-categories'],function () {
		Route::post('/', 'BranchesController@branchesCategories');

		Route::group(['prefix' => 'branches'],function () {
			Route::post('/', 'BranchesController@branches');
			Route::post('/branch', 'BranchesController@branch');
		});
	});

	Route::get('/about-us', 'AboutUsController@index');
	Route::post('/apply-for-a-loan', 'ApplyLoanController@index');
    Route::post('/renew-loan', 'RenewLoanController@index');
	Route::post('/refer-a-friend', 'ReferFriendController@index');
	Route::post('/write-my-story', 'WriteStoryController@index');
	Route::post('/contact-us', 'ContactController@index');
    Route::get('/governorates', 'GovernorateController@index');
    Route::get('/banners', 'BannerController@index');
    Route::get('/redeemable-items', 'RedeemableItemController@index');
    Route::post('/redeem', 'RedeemableItemController@redeem');
    Route::get('/e-wallet', 'EWaletController@index');

	/*
    |--------------------------------------------------------------------------
    | Middleware Routes
    |--------------------------------------------------------------------------
    */

    Route::post('login', 'VitasController@login');
    Route::get('logout', 'VitasController@logout');

    Route::group(['prefix' => 'profile'],function () {
    	Route::get('get', 'VitasController@profile');
        Route::post('edit', 'VitasController@editprofile');
    	Route::post('change_password', 'VitasController@change_password');
    	Route::post('forgot_password', 'VitasController@forgot_password');
    	Route::post('reset_password', 'VitasController@reset_password');
    });

    Route::post('verification', 'VitasController@verification');
    Route::get('active_loans', 'VitasController@active_loans');
    Route::get('closed_loans', 'VitasController@closed_loans');
    Route::get('statistics', 'VitasController@statistics');

    Route::post('zain-cash/customer-data-inquiry', 'VitasController@customer_data_inquiry');
    Route::post('zain-cash', 'VitasController@zain_cash');

	/*
    |--------------------------------------------------------------------------
    | Push Routes
    |--------------------------------------------------------------------------
    */

    Route::group(['prefix' => 'push'],function () {
    	Route::post('/set_user_push', ['uses' => 'PushController@set_user_push', 'as' => 'api.push.setToken']);
    	Route::post('/inbox', ['uses' => 'PushController@inbox', 'as' => 'api.push.inbox']);
    	Route::get('/unread_count', ['uses' => 'PushController@unread_count', 'as' => 'api.push.unread_count']);
    });

	/*
    |--------------------------------------------------------------------------
    | SMS Gateway
    |--------------------------------------------------------------------------
    */

    Route::post('sendsms', 'SendSMSController@index');

    /*
    |--------------------------------------------------------------------------
    | Loyalty Points Routes
    |--------------------------------------------------------------------------
    */

    Route::get('share-app', 'VitasController@shareapp');

});

Route::get('zain-cash-callback', 'Api\VitasController@zain_cash_callback')->name('zain-cash-callback');
Route::get('zain-cash-status', 'Api\VitasController@zain_cash_status')->name('zain-cash-status');


Route::post('api-push', 'Api\ApiPushController@index');
