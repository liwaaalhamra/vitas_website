<?php
use App\Http\Controllers\AccountController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*
|--------------------------------------------------------------------------
| CMS routes
|--------------------------------------------------------------------------
|
*/
Route::get('apply-for-a-loans/export/ok', 'Exports\ApplyForALoanExportController@export')->middleware('admin.user')->name('apply-for-a-loans.export');
Route::get('vacancies/export/ok', 'Exports\VacanciesExportController@export')->middleware('admin.user')->name('vacancies.export');
Route::get('contact-forms/export/ok', 'Exports\ContactFormsExportController@export')->middleware('admin.user')->name('contact-forms.export');

Route::get('check-eligible-users/export/ok', 'Exports\CheckEligibleUsersExportController@export')->middleware('admin.user')->name('check-eligible-users.export');
Route::get('refer-friends/export/ok', 'Exports\ReferFriendsExportController@export')->middleware('admin.user')->name('refer-friends.export');

Route::get('renew-loans/export/ok', 'Exports\RenewLoansExportController@export')->middleware('admin.user')->name('renew-loans.export');
Route::get('app-users/export/ok', 'Exports\AppUserExportController@export')->middleware('admin.user')->name('app-users.export');
Route::get('transactions/export/ok', 'Exports\TransactionExportController@export')->middleware('admin.user')->name('transactions.export');

Route::group(['prefix' => 'admin'], function () {
	Voyager::routes();

	Route::group(['middleware' => 'admin.user'], function(){
		Route::get('/push-notifications', 'Voyager\PushController@index');
		Route::post('/push-notification/send/push', 'Voyager\PushController@send_push')->name('send.push');
		Route::post('/push-notification/send/single-push', 'Voyager\PushController@send_single_push')->name('send.single.push');
		Route::get('/import-loyalty-points', 'Voyager\ImportLoyaltyPointController@index');
		Route::post('/import-loyalty-points', 'Voyager\ImportLoyaltyPointController@import')->name('import.loyalty');

		Route::get('/payments', 'Voyager\PaymentsAdminController@index')->name('admin.payments.index');
		Route::get('/payments/{payment}', 'Voyager\PaymentsAdminController@show')->name('admin.payments.show');
	});
});

/*
|--------------------------------------------------------------------------
| Website's routes
|--------------------------------------------------------------------------
|
*/

Route::get('/privacy-policy', function(){
	return view('vitas-privacy-policy');
});

Route::get('/app-stores', 'AppStoresController@index')->name('app-stores');

Route::get('/', function () {
	return redirect('ar/home');
});

/*
|--------------------------------------------------------------------------
| Website's routes with locale and website middleware
|--------------------------------------------------------------------------
|
*/

Route::get('/googlecc05cf8e8d002fd4.html',function () {
	return view('googlecc05cf8e8d002fd4');
});

Route::group(['middleware' => ['locale', 'website'], 'prefix' => '{locale}'], function(){

	Route::get('/', 'GeneralController@index');
    Route::match(['get', 'post', 'delete'], '/delete-account', [AccountController::class, 'handle'])->name('accounts.delete');

	Route::group(['prefix' => 'home'],function () {
		Route::get('/', 'HomeController@index')->name('home');
		Route::post('/check-loan', 'HomeController@checkLoan')->name("check-loan");
	});

	Route::get('/about-us', 'AboutController@index')->name('about');
	Route::get('/social-impact', 'SocialImpactController@index')->name('social-impact');
	Route::get('/success-stories/{slug}', 'SuccessStoriesController@single')->name('singleStory');

	Route::group(['prefix' => 'community-events'],function () {
		Route::get('/', 'CommunityEventsController@index')->name('community-events');
		Route::get('/{slug}', 'CommunityEventsController@single')->name('singleCommunity');
	});

	Route::group(['prefix' => 'products'],function () {
		Route::get('/', 'ProductsController@index')->name('products');
		Route::get('/{slug}', 'ProductsController@single')->name('singleProducts');
	});

	Route::group(['prefix' => 'apply-for-a-loan'],function () {
		Route::get('/{slug?}', 'ApplyLoanController@index')->name('view-apply-loan');
		Route::post('/', 'ApplyLoanController@apply')->name("apply-loan");
	});

	Route::group(['prefix' => 'careers'],function () {
		Route::get('/', 'CareersController@index')->name('careers');
		Route::get('/{slug}', 'CareersController@singleCareer')->name('singleCareer');
		Route::post('/careers-apply', 'CareersController@apply')->name("apply-career");
	});

	Route::group(['prefix' => 'apply-for-future-vacancies'],function () {
		Route::get('/', 'CareersController@futureVacancies')->name('futureVacancies');
		Route::post('/careers-future', 'CareersController@applyFuture')->name("apply-future-career");
	});

	Route::group(['prefix' => 'contact-us'],function () {
		Route::get('/', 'ContactController@index')->name('contact');
		Route::post('/', 'ContactController@contactUs')->name("send-contact");
	});

	Route::get('/locate-us', 'LocateUsController@index')->name('locate-us');
	Route::get('/faqs', 'FAQsController@index')->name('faqs');

	/*
	|--------------------------------------------------------------------------
	| Areeba payment (pay a loan installment by loan number)
	|--------------------------------------------------------------------------
	*/
	Route::get('/pay/loan', 'PaymentController@showLoanForm')->name('pay.loan.form');
	Route::post('/pay/loan', 'PaymentController@lookupAndInitiate')->name('pay.loan.init');
	Route::get('/pay/result', 'PaymentController@paymentReturn')->name('pay.result');

});

/*
|--------------------------------------------------------------------------
| Webviews
|--------------------------------------------------------------------------
*/

Route::get('/faqs/{lang}', 'FAQsController@webview');

Route::get('/terms-and-conditions/{lang}', 'TermsController@webview');

Route::get('/app-check-eligibility/{lang}', 'CheckEligibilityController@webview');
Route::post('/app-check-eligibility/{lang}', 'CheckEligibilityController@checkLoan')->name('app.checkLoan');

Route::get('/how-to-collect/{lang}', 'HowToCollect@webview');

