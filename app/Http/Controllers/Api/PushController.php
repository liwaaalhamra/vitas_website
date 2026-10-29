<?php

namespace App\Http\Controllers\api;

use App\Models\Api\Inbox;
use App\Models\Api\UserPush;
use App\Http\Requests\PushRequest;
use App\Helpers\PushHelper;
use App\Models\Api\Users;
use Carbon\Carbon;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;

class PushController extends Controller
{
    public function set_user_push(Request $request) {
        if($access_token = parent::valid_access_token($request)) {
            if($user = parent::get_user_by_access_token($access_token)) {
                // FCM registration token, "player_id" still accepted for older app builds
                $fcm_token = $request['fcm_token'] ?: $request['player_id'];
                if(empty($fcm_token)){
                    return parent::return_error('FCM Token required!',400,'messages.missing_parameter');
                }
                if(!parent::validate_fcm_token($fcm_token)){
                    return parent::return_error('Invalid FCM Token',401,'messages.invalid_parameters');
                }
                // a device token belongs to one user only (logout / login with another account)
                UserPush::where('player_id', $fcm_token)->where('users_id', '<>', $user->id)->delete();
                if($user_push = UserPush::whereUsersId($user->id)->first()){
                    $user_push->player_id = $fcm_token;
                    $user_push->save();
                }else{
                    $user_push = UserPush::create([
                        'player_id' => $fcm_token,
                        'users_id' => $user->id
                    ]);
                }
                $message = utf8_decode(utf8_encode(Lang::get('messages.request_submitted_successfully')));
                return response()->json(['status' => $message]);
            }else{
                return parent::return_error("Invalid Access Token", 401, 'messages.invalid_access_token');
            }
        }else{
            return parent::return_error("Missing Access Token", 102, 'messages.authentication_missing');
        }
    }

    public function inbox(Request $request) {
        if($access_token = parent::valid_access_token($request)) {
            if($user = parent::get_user_by_access_token($access_token)) {
                Inbox::where(function ($query) use($user) {
                    $query->where(function ($query) use($user) {
                        $query->whereUserId($user->id)->whereType('targeted');
                    })->orwhere('type','=','bulk');
                })->update(['is_read' => 1]);
                $query = Inbox::where(function ($query) use($user) {
                    $query->where(function ($query) use($user) {
                        $query->whereUserId($user->id)->whereType('targeted');
                    })->orwhere('type','=','bulk');
                })->latest();
                if($request['search']){
                    $search = $request['search'];
                    $query->where(function($query) use ($search){
                        $query->whereRaw("LOWER(title) like ? ", [ "%".strtolower($search)."%"])
                            ->orwhereRaw("LOWER(message) like ? ", [ "%".strtolower($search)."%"]);
                    });
                }
                $messages = $query->orderBy('id', 'desc')->take(10)->get();
                return response()->json($messages);
            }else{
                return parent::return_error("Invalid Access Token", 401, 'messages.invalid_access_token');
            }
        }else{
            return parent::return_error("Missing Access Token", 102, 'messages.authentication_missing');
        }
    }

    public function unread_count(Request $request) {
        if($access_token = parent::valid_access_token($request)) {
            if($user = parent::get_user_by_access_token($access_token)) {
                $count = Inbox::whereIsRead(0)->where(function ($query) use($user) {
                    $query->where(function ($query) use($user) {
                        $query->whereUserId($user->id)->whereType('targeted');
                    })->orwhere('type','=','bulk');
                })->count();
                return response()->json(['unread_count' => $count]);
            }else{
                return parent::return_error("Invalid Access Token", 401, 'messages.invalid_access_token');
            }
        }else{
            return parent::return_error("Missing Access Token", 102, 'messages.authentication_missing');
        }
    }

    public function send_push(Request $request){
        if(!Input::has('title')){
            return parent::return_error('Title required!',400,'messages.missing_parameter');
        }
        if(!Input::has('message')){
            return parent::return_error('Message required!',400,'messages.missing_parameter');
        }
        if(!Input::has('employee_id')){
            return parent::return_error('Employee ID required!',400,'messages.missing_parameter');
        }
        if($employee = Users::where('employee_id','=',$request->employee_id)->where('is_blocked','=',0)->first()){
            if($user_push = UserPush::whereUsersId($employee->id)->first()){
                $info['title'] = trim($request->title);
                $info['body'] = trim($request->message);
                $info['tokens'] = [$user_push->player_id];
                // FCM has no scheduled delivery ("send_after"), the push is sent immediately
                $result = parent::sendPush($info);
            }
        }
    }

    public function sync_users(Request $request){
        foreach ($request->all() as $user){
            if(!isset($user['employee_id'])){
                continue;
            }
            if($existing_user = Users::where('employee_id','=',intval($user['employee_id']))->first()){
                if(isset($user['first_name'])){
                    $existing_user->first_name = $user['first_name'];
                }
                if(isset($user['last_name'])){
                    $existing_user->last_name = $user['last_name'];
                }
                if(isset($user['department'])){
                    $existing_user->department = $user['department'];
                }
                if(isset($user['cost_center'])){
                    $existing_user->cost_center = $user['cost_center'];
                }
                if(isset($user['job_title'])){
                    $existing_user->job_title = $user['job_title'];
                }
                if(isset($user['primary_phone'])){
                    $existing_user->primary_phone = $user['primary_phone'];
                }
                if(isset($user['secondary_phone'])){
                    $existing_user->secondary_phone = $user['secondary_phone'];
                }
                if(isset($user['street'])){
                    $existing_user->street = $user['street'];
                }
                if(isset($user['building'])){
                    $existing_user->building = $user['building'];
                }
                if(isset($user['floor'])){
                    $existing_user->floor = $user['floor'];
                }
                if(isset($user['gender'])){
                    $existing_user->gender = $user['gender'];
                }
                if(isset($user['dob'])){
                    $existing_user->dob = $user['dob'];
                }
                if(isset($user['address'])){
                    $existing_user->address = $user['address'];
                }
                if(isset($user['email'])){
                    $existing_user->email = $user['email'];
                }
                if(isset($user['is_blocked'])){
                    if($user['is_blocked'] == 'true'){
                        $existing_user->is_blocked = true;
                    }else{
                        $existing_user->is_blocked = false;
                    }
                }
                $existing_user->save();
            }else{
                $generated_password = str_random(10);
                $new_user = Users::create([
                    'employee_id' => intval($user['employee_id']),
                    'first_name' => isset($user['first_name']) ? $user['first_name'] : null,
                    'last_name' => isset($user['last_name']) ? $user['last_name'] : null,
                    'cost_center' => isset($user['cost_center']) ? $user['cost_center'] : null,
                    'department' => isset($user['department']) ? $user['department'] : null,
                    'job_title' => isset($user['job_title']) ? $user['job_title'] : null,
                    'primary_phone' => isset($user['primary_phone']) ? $user['primary_phone'] : null,
                    'secondary_phone' => isset($user['secondary_phone']) ? $user['secondary_phone'] : null,
                    'street' => isset($user['street']) ? $user['street'] : null,
                    'building' => isset($user['building']) ? $user['building'] : null,
                    'floor' => isset($user['floor']) ? $user['floor'] : null,
                    'gender' => isset($user['gender']) ? $user['gender'] : null,
                    'dob' => isset($user['dob']) ? $user['dob'] : null,
                    'address' => isset($user['address']) ? $user['address'] : null,
                    'email' => isset($user['email']) ? $user['email'] : null,
                    'password' => $generated_password,
                    'password_clear_text' => $generated_password
                ]);

                if(isset($user['email'])){
                    if(filter_var($user['email'], FILTER_VALIDATE_EMAIL)){
                        $data = [
                            'to' => $user['email'],
                            'full_name' => isset($user['full_name']) ? $user['full_name'] : 'Sir/Ms',
                            'title' => 'Welcome',
                            'generated_password' => $generated_password,
                            'message1' => 'Welcome to Mcdonalds app, please use the following password:',
                            'message3' => utf8_decode(utf8_encode(Lang::get('messages.sincerely'))),
                            'message4' => utf8_decode(utf8_encode(Lang::get('messages.team'))),
                        ];

                        $body = view('emails.welcome', [
                            'to' => $user['email'],
                            'full_name' => isset($user['full_name']) ? $user['full_name'] : 'Sir/Ms',
                            'title' => 'Welcome',
                            'generated_password' => $generated_password,
                            'message1' => 'Welcome to Mcdonalds app, please use the following password:',
                            'message3' => utf8_decode(utf8_encode(Lang::get('messages.sincerely'))),
                            'message4' => utf8_decode(utf8_encode(Lang::get('messages.team'))),
                        ])->render();

                        $this->send_email($data['to'],env('APP_NAME') .' | '.$data['title'],$body);

                        // Mail::send('emails.welcome', ['data' => $data], function ($message) use ($data) {
                        //     $message->subject(env('APP_NAME') .' | '.$data['title']);
                        //     $message->to($data['to']);
                        // });
                    }

                }
            }
        }
        return response()->json(['status' => 'Users updated successfully']);
    }

}
