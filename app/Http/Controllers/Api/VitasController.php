<?php

namespace App\Http\Controllers\Api;

use App\Rate;
use App\Redeem;
use App\Transaction;
use GuzzleHttp\Client;
use App\RedeemableItem;
use Illuminate\Support\Str;
use App\Models\Api\AppUsers;
use App\Models\Api\UserPush;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Api\AppUsersPoint;
use App\Models\Api\AppUsersAccess;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;

class VitasController extends Controller
{
    private $sendSMSController;

    public function __construct(SendSMSController $sendSMSController)
    {
        $this->sendSMSController = $sendSMSController;
    }

    /**
     * Login
     * @param Request $request
     * @return \stdClass
     */
    public function login(Request $request)
    {
        try {

            if (empty($request['username'])) {
                return parent::return_error("Missing Username", 401, 'messages.missing_parameter');
            }
            if (empty($request['password'])) {
                return parent::return_error("Missing Password", 401, 'messages.missing_parameter');
            }
            $info = [];
            $info['function'] = 'ClientLogin';
            $info['params']['Username'] = trim($request->username);
            $info['params']['Password'] = trim($request->password);
            $info['params']['AppUsername'] = env('APP_USERNAME');
            $info['params']['AppPassword'] = env('APP_PASSWORD');

            $response = parent::middleware_connect($info);
            if ($response == false) {
                return parent::return_error('Failed To Connect', 456, 'Failed To Connect');
            }
            if ($response['StatusCode'] != 0) {
                return parent::return_error($response['ActionStatus'], 457, $response['ActionStatus']);
            }
            if (!$user = AppUsers::where('employee_id', '=', $request->username)->first()) {
                $user = AppUsers::create(['employee_id' => $request->username]);
            }

            AppUsersAccess::where("user_id", "=", $user->id)->delete();
            $access_token = Str::random(64);
            AppUsersAccess::create([
                'access_token' => $access_token,
                'user_id' => $user->id
            ]);

            // Loyalty Points
            if (!$user_points = AppUsersPoint::where([['user_id', $user->employee_id], ['meta_name', 'install_our_app']])->first()) {
                $user_point_1 = new AppUsersPoint;
                $user_point_1->user_id = $user->employee_id;
                $user_point_1->meta_name = "install_our_app";
                $user_point_1->points = 150;
                $user_point_1->description = "First time login";
                $user_point_1->save();

                $user_point_2 = new AppUsersPoint;
                $user_point_2->user_id = $user->employee_id;
                $user_point_2->meta_name = "daily_log_in";
                $user_point_2->points = 10;
                $user_point_2->description = "First log in of the day";
                $user_point_2->save();

                $total_points = $user_point_1->points + $user_point_2->points;

                if ($user_total_points = AppUsers::where('employee_id', $user->employee_id)->first()) {
                    $user_total_points->total_points = $total_points;
                    $user_total_points->save();
                }
            }

            $now = Carbon::now()->format('Y-m-d');
            if (!$user_points = AppUsersPoint::whereDate('created_at', $now)->where([['user_id', $user->employee_id], ['meta_name', 'daily_log_in']])->first()) {
                $user_points = new AppUsersPoint;
                $user_points->user_id = $user->employee_id;
                $user_points->meta_name = "daily_log_in";
                $user_points->points = 10;
                $user_points->description = "First log in of the day";
                $user_points->save();

                if ($user_total_points = AppUsers::where('employee_id', $user->employee_id)->first()) {
                    $user_total_points->total_points = $user_total_points->total_points + $user_points->points;
                    $user_total_points->save();
                }
            }

            $user->token = $access_token;
            unset($user->access_token);
            return parent::return_json($user);
        } catch (\Exception $e) {
            Log::error('Error on login', [$e]);
        }
    }

    public function shareapp(Request $request)
    {
        if ($access_token = parent::valid_access_token($request)) {
            if ($user = parent::get_user_by_access_token($access_token)) {

                $now = Carbon::now()->format('Y-m-d');
                if (!$user_points = AppUsersPoint::whereDate('created_at', $now)->where([['user_id', $user->employee_id], ['meta_name', 'share_our_app']])->first()) {
                    $user_points = new AppUsersPoint;
                    $user_points->user_id = $user->employee_id;
                    $user_points->meta_name = "share_our_app";
                    $user_points->points = 10;
                    $user_points->description = "Share our app 1/year";
                    $user_points->save();

                    if ($user_total_points = AppUsers::where('employee_id', $user->employee_id)->first()) {
                        $user_total_points->total_points = $user_total_points->total_points + $user_points->points;
                        $user_total_points->save();
                    }
                }

                return [
                    'code' => 1,
                    'message' => trans('messages.request_successful')
                ];
            }
        }
    }

    /**
     * Log Out
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function logout(Request $request)
    {
        if ($access_token = parent::valid_access_token($request)) {
            if ($user = parent::get_user_by_access_token($access_token)) {
                AppUsersAccess::where("user_id", "=", $user->id)->delete();
                UserPush::where("users_id", "=", $user->id)->delete();
                $message = utf8_decode(utf8_encode(Lang::get('messages.logout_success')));
                return parent::return_json(['status' => $message]);
            } else {
                return parent::return_error("Invalid Access Token", 401, 'messages.invalid_access_token');
            }
        } else {
            return parent::return_error("Missing Access Token", 102, 'messages.authentication_missing');
        }
    }

    /**
     * Get Profile
     * @param Request $request
     * @return \stdClass
     */
    public function profile(Request $request)
    {
        if ($access_token = parent::valid_access_token($request)) {
            if ($user = parent::get_user_by_access_token($access_token)) {
                $info = [];
                $info['function'] = 'GetClientData';
                $info['params']['ClientID'] = $user->employee_id;
                $info['params']['AppUsername'] = env('APP_USERNAME');
                $info['params']['AppPassword'] = env('APP_PASSWORD');
                $response = parent::middleware_connect($info);
                if ($response == false) {
                    return parent::return_error('Failed To Connect', 456, 'Failed To Connect');
                }
                if ($response['StatusCode'] != 0) {
                    return parent::return_error($response['ActionStatus'], 457, $response['ActionStatus']);
                }

                // Send User Points With getProfile API
                $user_points = AppUsers::select([
                    'total_points'
                ])->where('employee_id', $user->employee_id)
                    ->first();

                // Get Redeem Id
                $redeem = Redeem::where([['user_id', $user->employee_id], ['redeem', 1], ['redeemed', 0]])->first();
                if ($redeem) {
                    // The Item is redeemed
                    $redeem['redeemed'] = 1;
                    $redeem->save();

                    // Get Redeemable Item Id
                    $item = RedeemableItem::select([
                        'id',
                        'points'
                    ])->findOrFail($redeem['redeemable_item_id']);

                    // Reduce points
                    $new_total_points = $user_points['total_points'] - $item['points'];

                    // Set new total points
                    $user['total_points'] = $new_total_points;
                    // The user can redeem now
                    $user['can_redeem'] = 1;
                    $user->save();
                }

                //Unredeem
                $unredeem = Redeem::where([['user_id', $user->employee_id], ['redeem', 0], ['redeemed', 1]])->first();
                if ($unredeem) {
                    // The Item is redeemed
                    $unredeem['redeemed'] = 0;
                    $unredeem->save();

                    // Get Redeemable Item Id
                    $item = RedeemableItem::select([
                        'id',
                        'points'
                    ])->findOrFail($unredeem['redeemable_item_id']);

                    // Reduce points
                    $new_total_points = $user_points['total_points'] + $item['points'];

                    // Set new total points
                    $user['total_points'] = $new_total_points;
                    // The user can redeem now
                    $user['can_redeem'] = 1;
                    $user->save();
                }

                $result = $response['ClientData'];
                $result['total_points'] = ($redeem || $unredeem ? $new_total_points : $user_points['total_points']);

                // Store results in database
                $user->customer_name = $result['CustomerName'];
                $user->first_name = $result['FirstName'];
                $user->english_name = $result['EnglishName'];
                $user->mobile_number = $result['Mobile'];
                $user->email = $result['Email'];
                $user->quarter = $result['Quarter'];
                $user->town = $result['Town'];
                $user->governorate = $result['Governorate'];
                $user->gender = $result['Gender'];
                $user->save();

                // $result['access_token'] = $access_token;
                return response()->json($result);
            } else {
                return parent::return_error("Invalid Access Token", 401, 'messages.invalid_access_token');
            }
        } else {
            return parent::return_error("Missing Access Token", 102, 'messages.authentication_missing');
        }
    }

    /**
     * Edit Profile
     * @param Request $request
     * @return \stdClass
     */
    public function editprofile(Request $request)
    {
        if ($access_token = parent::valid_access_token($request)) {
            if ($user = parent::get_user_by_access_token($access_token)) {

                if (empty(request('full_name'))) {
                    return parent::return_error("Missing Full Name", 401, 'messages.missing_parameter');
                }
                if (empty(request('phone_number'))) {
                    return parent::return_error("Missing Phone Number", 401, 'messages.missing_parameter');
                }
                // if(empty(request('email_address'))){
                //     return parent::return_error("Missing Email Address", 401, 'messages.missing_parameter');
                // }
                if (empty(request('gender'))) {
                    return parent::return_error("Missing Gender", 401, 'messages.missing_parameter');
                }
                if (empty(request('governorate'))) {
                    return parent::return_error("Missing Gender", 401, 'messages.missing_parameter');
                }
                if (empty(request('address'))) {
                    return parent::return_error("Missing Address", 401, 'messages.missing_parameter');
                }

                $body = view('emails/edit-profile', [
                    'full_name' => $request['full_name'],
                    'phone_number' => $request['phone_number'],
                    'place_of_residence' => $request['place_of_residence'],
                    'email_address' => $request['email_address'],
                    'gender' => $request['gender'],
                    'governorate' => $request['governorate'],
                    'address' => $request['address'],
                    'username' => $request['username'],
                    'user_name' => $user['english_name'],
                    'user_employer_id' => $user['employee_id']
                ])->render();

                $this->send_email('info@vitasiraq.com','Application Request Profile Edit',$body);

                // Mail::send('emails/edit-profile', [
                //     'full_name' => $request['full_name'],
                //     'phone_number' => $request['phone_number'],
                //     'place_of_residence' => $request['place_of_residence'],
                //     'email_address' => $request['email_address'],
                //     'gender' => $request['gender'],
                //     'governorate' => $request['governorate'],
                //     'address' => $request['address'],
                //     'username' => $request['username'],
                //     'user_name' => $user['english_name'],
                //     'user_employer_id' => $user['employee_id']
                // ], function ($message) use ($user) {
                //     $message->from('noreply@tedmob.com', 'Application Vitas');
                //     $message->to('info@vitasiraq.com')->subject('Application Request Profile Edit');
                // });

                return [
                    'code' => 1,
                    'message' => trans('messages.request_successful')
                ];
            } else {
                return parent::return_error("Invalid Access Token", 401, 'messages.invalid_access_token');
            }
        } else {
            return parent::return_error("Missing Access Token", 102, 'messages.authentication_missing');
        }
    }

    /**
     * Change Password
     * @param Request $request
     * @return \stdClass
     */
    public function change_password(Request $request)
    {
        if ($access_token = parent::valid_access_token($request)) {
            if ($user = parent::get_user_by_access_token($access_token)) {
                if (empty($request['old_password'])) {
                    return parent::return_error("Missing Old Password", 401, 'messages.missing_parameter');
                }
                if (empty($request['new_password'])) {
                    return parent::return_error("Missing New Password", 401, 'messages.missing_parameter');
                }
                $info = [];
                $info['function'] = 'ChangePassword';
                $info['params']['Username'] = $user->employee_id;
                $info['params']['OldPassword'] = $request->old_password;
                $info['params']['NewPassword'] = $request->new_password;
                $info['params']['AppUsername'] = env('APP_USERNAME');
                $info['params']['AppPassword'] = env('APP_PASSWORD');
                $response = parent::middleware_connect($info);
                if ($response == false) {
                    return parent::return_error('Failed To Connect', 456, 'Failed To Connect');
                }
                if ($response['StatusCode'] != 0) {
                    return parent::return_error($response['ActionStatus'], 457, $response['ActionStatus']);
                }
                return response()->json(['status' => trans('messages.request_successful')]);
            } else {
                return parent::return_error("Invalid Access Token", 401, 'messages.invalid_access_token');
            }
        } else {
            return parent::return_error("Missing Access Token", 102, 'messages.authentication_missing');
        }
    }

    /**
     * Forgot Password
     * @param Request $request
     * @return \stdClass
     */
    public function forgot_password(Request $request)
    {
        if (empty($request['mobile'])) {
            return parent::return_error("Missing Mobile", 401, 'messages.missing_parameter');
        }
        if (empty($request['username'])) {
            return parent::return_error("Missing Username", 401, 'messages.missing_parameter');
        }
        $info = [];
        $info['function'] = 'ForgotPassword';
        $info['params']['ClientID'] = $request->username;
        $info['params']['Mobile'] = $request->mobile;
        $info['params']['AppUsername'] = env('APP_USERNAME');
        $info['params']['AppPassword'] = env('APP_PASSWORD');
        $response = parent::middleware_connect($info);
        if ($response == false) {
            return parent::return_error('Failed To Connect', 456, 'Failed To Connect');
        }
        if ($response['StatusCode'] != 0) {
            return parent::return_error($response['ActionStatus'], 457, $response['ActionStatus']);
        }
        if (!$user = AppUsers::where('employee_id', '=', $request->username)->first()) {
            return parent::return_error("User does not exist", 404, 'messages.invalid_credentials');
        }
        $reset_code = parent::number_generator(4);
        $registration = parent::generate_tokens(4);
        $user->registration = $registration;
        $user->reset_code = $reset_code;
        $user->save();

        $sendSMS = $this->sendSMSController->index($request->mobile, $reset_code);

        $return_body["pin"] = $reset_code;
        $return_body["registration"] = $registration;
        return response()->json($return_body);
    }

    /**
     * PIN Verification
     * @param Request $request
     * @return \stdClass
     */
    public function verification(Request $request)
    {
        if (empty($request['registration'])) {
            return parent::return_error("Invalid User", 401, 'messages.missing_parameter');
        }
        if (empty($request['reset_code'])) {
            return parent::return_error("Missing PIN", 401, 'messages.missing_parameter');
        }

        if (!$user = AppUsers::where('registration', '=', $request->registration)->where('reset_code', '=', $request->reset_code)->first()) {
            return parent::return_error("Wrong PIN ", 404, 'messages.invalid_credentials');
        }
        $user->registration = null;

        return response()->json(['status' => 'PIN Verified Successfully']);
    }

    /**
     * Reset Password
     * @param Request $request
     * @return \stdClass
     */
    public function reset_password(Request $request)
    {
        if (empty($request['new_password'])) {
            return parent::return_error("Missing New Password", 401, 'messages.missing_parameter');
        }
        if (empty($request->get('reset_code'))) {
            return parent::return_error("Missing Reset Code", 401, 'messages.missing_parameter');
        }
        if (empty($request->get('registration'))) {
            return parent::return_error("Missing Registration", 401, 'messages.missing_parameter');
        }
        if (!$user = AppUsers::where('registration', '=', $request->registration)->where('reset_code', '=', $request->reset_code)->first()) {
            return parent::return_error("Wrong Code", 406, 'messages.invalid_code');
        }
        $info = [];
        $info['function'] = 'ChangeForgottenPassword';
        $info['params']['Username'] = $user->employee_id;
        $info['params']['NewPassword'] = $request->new_password;
        $info['params']['AppUsername'] = env('APP_USERNAME');
        $info['params']['AppPassword'] = env('APP_PASSWORD');
        $response = parent::middleware_connect($info);
        if ($response == false) {
            return parent::return_error('Failed To Connect', 456, 'Failed To Connect');
        }
        if ($response['StatusCode'] != 0) {
            return parent::return_error($response['ActionStatus'], 457, $response['ActionStatus']);
        }
        $user->reset_code = null;
        $user->save();
        AppUsersAccess::where("user_id", "=", $user->id)->delete();
        $access_token = Str::random(64);
        AppUsersAccess::create([
            'access_token' => $access_token,
            'user_id' => $user->id
        ]);
        return response()->json(['status' => trans('messages.request_successful')]);
    }

    /**
     * Get Statistics
     * @param Request $request
     * @return \stdClass
     */
    public function statistics(Request $request)
    {
        $info = [];
        $info['function'] = 'GetStatistics';
        $info['params']['AppUsername'] = env('APP_USERNAME');
        $info['params']['AppPassword'] = env('APP_PASSWORD');
        $response = parent::middleware_connect($info);
        if ($response == false) {
            return parent::return_error('Failed To Connect', 456, 'Failed To Connect');
        }
        if ($response['StatusCode'] != 0) {
            return parent::return_error($response['ActionStatus'], 457, $response['ActionStatus']);
        }
        return response()->json($response['Statistics']);
    }

    /**
     * Get Closed Loans
     * @param Request $request
     * @return \stdClass
     */
    public function closed_loans(Request $request)
    {
        if ($access_token = parent::valid_access_token($request)) {
            if ($user = parent::get_user_by_access_token($access_token)) {
                $info = [];
                $info['function'] = 'GetClosedLoans';
                $info['params']['ClientID'] = $user->employee_id;
                $info['params']['AppUsername'] = env('APP_USERNAME');
                $info['params']['AppPassword'] = env('APP_PASSWORD');
                $response = parent::middleware_connect($info);
                if ($response == false) {
                    return parent::return_error('Failed To Connect', 456, 'Failed To Connect');
                }
                if ($response['StatusCode'] != 0) {
                    return parent::return_error($response['ActionStatus'], 457, $response['ActionStatus']);
                }

                // Map through the ClosedLoans and update LoanAmount
                $mappedLoans = array_map(function($loan) {
                    $loan['LoanAmount'] = (int) round($loan['LoanAmount']);
                    $loan['InstallmentAmount'] = (int) round($loan['InstallmentAmount']);
                    return $loan;
                }, $response['ClosedLoans']);

                // Return the modified response
                $response['ClosedLoans'] = $mappedLoans;

                return response()->json($response['ClosedLoans']);
            } else {
                return parent::return_error("Invalid Access Token", 401, 'messages.invalid_access_token');
            }
        } else {
            return parent::return_error("Missing Access Token", 102, 'messages.authentication_missing');
        }
    }

    /**
     * Get Active Loans
     * @param Request $request
     * @return \stdClass
     */
    public function active_loans(Request $request)
    {
        if ($access_token = parent::valid_access_token($request)) {
            if ($user = parent::get_user_by_access_token($access_token)) {
                try {
                    $info = [];
                    $info['function'] = 'GetActiveLoan';
                    $info['params']['ClientID'] = $user->employee_id;
                    $info['params']['AppUsername'] = env('APP_USERNAME');
                    $info['params']['AppPassword'] = env('APP_PASSWORD');
                    $response = parent::middleware_connect($info);
                    if ($response == false) {
                        return parent::return_error('Failed To Connect', 456, 'Failed To Connect');
                    }
                    if ($response['StatusCode'] != 0) {
                        if ($response['StatusCode'] == 9) { //return empty array
                            return response()->json(null);
                        }
                        return parent::return_error($response['ActionStatus'], 457, $response['ActionStatus']);
                    }
                    if (isset($response['ActiveLoan']['LoanProduct']) && str_contains($response['ActiveLoan']['LoanProduct'], 'Dinar')) {
                        $response['ActiveLoan']['currency'] = trans('messages.IQD');
                    } else {
                        $response['ActiveLoan']['currency'] = '$';
                    }

                    $response['ActiveLoan']['LoanAmount'] = (int) round($response['ActiveLoan']['LoanAmount']);

                    return response()->json($response['ActiveLoan']);
                } catch (\Exception $e) {
                    return parent::return_error("Error from vitas server", 101, $e->getMessage());
                }
            } else {
                return parent::return_error("Invalid Access Token", 401, 'messages.invalid_access_token');
            }
        } else {
            return parent::return_error("Missing Access Token", 102, 'messages.authentication_missing');
        }
    }

    /**
     * Zain Cash CustomerDataInquiry
     * @param Request $request
     * @return \stdClass
     */
    public function customer_data_inquiry(Request $request)
    {
        if ($access_token = parent::valid_access_token($request)) {
            if ($user = parent::get_user_by_access_token($access_token)) {
                try {
                    $info = [];
                    $info['function'] = 'GetActiveLoan';
                    $info['params']['ClientID'] = $user->employee_id;
                    $info['params']['AppUsername'] = env('APP_USERNAME');
                    $info['params']['AppPassword'] = env('APP_PASSWORD');
                    $response = parent::middleware_connect($info);
                    if ($response == false) {
                        return parent::return_error('Failed To Connect', 456, 'Failed To Connect');
                    }
                    if ($response['StatusCode'] != 0) {
                        if ($response['StatusCode'] == 9) { //return empty array
                            return response()->json(null);
                        }
                        return parent::return_error($response['ActionStatus'], 457, $response['ActionStatus']);
                    }

                    // Get Loan Number for the user
                    $loanNumber = $response['ActiveLoan']['LoanNumber'];

                    // Customer data inquiry API From Zain
                    $client = new Client();
                    $response = $client->request('POST', 'https://go.web-abacus.com/api/VitasPayments/CustomerDataInquiry', array(
                        'form_params' => array(
                            'loanNumber' => $loanNumber,
                            'username' => env('APP_USERNAME'),
                            'password' => env('APP_PASSWORD')
                        )
                    ));
                    $result = json_decode($response->getBody(), true);
                    \Log::info('Results returned from api: ',[
                        $result,
                        $loanNumber
                    ]);
                    $result['LoanPaymentDue'] = $result['LoanCurrency'] == 'USD' ? $result['PaymentDueUSD'] : $result['PaymentDueIQD'];
                    $result['LateFeeDueUSD'] = (int) $result['LateFeeDueUSD'] ?? null;
                    $result['TotalOutstandingIQD'] = (int) $result['TotalOutstandingIQD'] ?? null;
                    $result['TotalOutstandingIQ1'] = (int) $result['TotalOutstandingIQ1'] ?? null;
                    
                    
                    $lang = App::getLocale();
                    $result['LoanCurrency'] = $result['LoanCurrency'] == 'IQD' ? trans('messages.IQD') : '$';
                    return response()->json($result);
                } catch (\Exception $e) {
                    Log::error('GetActiveLoan error: ' . $e->getMessage(), [
                        'trace' => $e->getTraceAsString(),
                    ]);

                    return parent::return_error('Invalid response', 400, 'Invalid response');
                }
            } else {
                return parent::return_error("Invalid Access Token", 401, 'messages.invalid_access_token');
            }
        } else {
            return parent::return_error("Missing Access Token", 102, 'messages.authentication_missing');
        }
    }

    /**
     * Zain Cash Init
     * @param Request $request
     * @return \stdClass
     */
    public function zain_cash(Request $request)
    {
        if ($access_token = parent::valid_access_token($request)) {
            if ($user = parent::get_user_by_access_token($access_token)) {
                $info = [];
                $info['function'] = 'GetActiveLoan';
                $info['params']['ClientID'] = $user->employee_id;
                $info['params']['AppUsername'] = env('APP_USERNAME');
                $info['params']['AppPassword'] = env('APP_PASSWORD');
                $response = parent::middleware_connect($info);
                if ($response == false) {
                    return parent::return_error('Failed To Connect', 456, 'Failed To Connect');
                }
                if ($response['StatusCode'] != 0) {
                    if ($response['StatusCode'] == 9) { //return empty array
                        return response()->json(null);
                    }
                    return parent::return_error($response['ActionStatus'], 457, $response['ActionStatus']);
                }

                // Get Loan Number for the user
                $loanNumber = $response['ActiveLoan']['LoanNumber'];

                // Customer data inquiry API From Zain
                $client = new Client();
                $response = $client->request('POST', 'https://go.web-abacus.com/api/VitasPayments/CustomerDataInquiry', array(
                    'form_params' => array(
                        'loanNumber' => $loanNumber,
                        'username' => env('ZAIN_CASH_USERNAME'),
                        'password' => env('ZAIN_CASH_PASSWORD')
                    )
                ));
                $result = json_decode($response->getBody(), true);

                // Store the transaction
                $transaction = new Transaction;
                $transaction->user_id = $result['ClientID'];
                $transaction->phone_number = $result['PhoneNumber'];
                $transaction->installment_number = $result['PaymentNumber'];
                $transaction->amount_iqd = $result['TotalPaymentDueIQD'];
                $transaction->amount_usd = $result['TotalPaymentDueUSD'];
                $transaction->branch_code = $result['BranchCode'];
                $transaction->loan_number = $result['LoanNumber'];
                $transaction->save();

                $secret = '$2y$10$NE7h9azOGsN4UwJkpd9ZWOTtkvLjy3UkxtRgHJaa8ufYzhkQoFCZa';

                // Create token header as a JSON string
                $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);

                $rate = Rate::findOrFail(1);
                // $amount_iraqi = $amount * $rate->rate;
                // Create token payload as a JSON string
                $payload = json_encode([
                    'amount' => $transaction->amount_iqd,
                    // 'serviceType' => 'Vitas',
                    'serviceType' => 'exchange rate: ' . /*$rate->rate*/ $result['ExchangeRate'] . ' | amount paid: ' . $result['TotalPaymentDueIQD'] . ' | call back Number: ' . $result['PhoneNumber'] . ' | channel type: client_mobile_app | Reference number: ' . $result['LoanNumber'] . ' | Branch Code:' . $result['BranchCode'] . '',
                    'msisdn' => 9647800416424,
                    'orderId' => $transaction->id,
                    'redirectUrl' => route('zain-cash-callback')
                    // 'iat' => 1597764363,
                    // 'exp' => 1597775163
                ]);

                // Encode Header to Base64Url String
                $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));

                // Encode Payload to Base64Url String
                $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

                // Create Signature Hash
                $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);

                // Encode Signature to Base64Url String
                $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

                // Create JWT
                $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

                $token = $jwt;
                $merchantId = '5c598f885b6eab6544f90e79';
                $lang = App::getLocale();

                $client = new Client();
                $response = $client->request('POST', 'https://api.zaincash.iq/transaction/init', array(
                    'form_params' => array(
                        'token' => $token,
                        'merchantId' => $merchantId,
                        'lang' => $lang
                    )
                ));
                $result = json_decode($response->getBody(), true);
                try {
                    $link = [
                        'link' => 'https://api.zaincash.iq/transaction/pay?id=' . $result['id']
                    ];

                    return response()->json($link);
                } catch (\Exception $ex) {
                    $result['error'] = 'Exception Error: ' . $result['err']['msg'];
                    return parent::return_error($result['error'], 101,  $result['error']);
                }
            } else {
                return parent::return_error("Invalid Access Token", 401, 'messages.invalid_access_token');
            }
        } else {
            return parent::return_error("Missing Access Token", 102, 'messages.authentication_missing');
        }
    }

    public function zain_cash_callback(Request $request)
    {

        $token = $request['token'];

        $tokenParts = explode(".", $token);
        $tokenHeader = base64_decode($tokenParts[0]);
        $tokenPayload = base64_decode($tokenParts[1]);
        $jwtHeader = json_decode($tokenHeader);
        $jwtPayload = json_decode($tokenPayload);

        if ($jwtPayload->status == 'success') {
            $transaction = Transaction::where('id', $jwtPayload->orderid)->firstOrFail();
            $transaction->status = 1;
            $transaction->save();

            $user_points = new AppUsersPoint;
            $user_points->user_id = $transaction->user_id;
            $user_points->meta_name = "zain_cash";
            $user_points->meta_value = $jwtPayload->orderid;
            $user_points->points = 150;
            $user_points->description = "Meta value is the order id";
            $user_points->save();

            if ($user_total_points = AppUsers::where('employee_id', $transaction->user_id)->first()) {
                $user_total_points->total_points = $user_total_points->total_points + $user_points->points;
                $user_total_points->save();
            }

            // Send all the data to Zain Cash
            $client = new Client();
            $response = $client->request('POST', 'https://go.web-abacus.com/api/ZainCash/CustomerPaymentDetails', array(
                'form_params' => array(
                    'LoanNumber' => (int)$transaction['loan_number'],
                    'DueAmount' => $transaction['amount_usd'],
                    'PaidAmount' => $transaction['amount_usd'],
                    'TransxsID' => $transaction['id'],
                    'PaymentDate' => date($transaction['created_at']),
                    'username' => env('ZAIN_CASH_USERNAME'),
                    'password' => env('ZAIN_CASH_PASSWORD')
                )
            ));
            $result = json_decode($response->getBody(), true);

            return redirect()->route('zain-cash-status', ['success=1', 'message=Payment completed successfully!']);
        } elseif ($jwtPayload->status == 'failed') {
            return redirect()->route('zain-cash-status', ['success=0', 'message=' . $jwtPayload->msg]);
        }
    }

    // Do not remove this function
    public function zain_cash_status() {}
}
