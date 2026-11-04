<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Api\AppUsers;
use App\Models\Api\UserPush;
use App\Models\Api\Inbox;

class ApiPushController extends Controller
{

	public function index(Request $request)
	{
        // Chayma2 20620

		if(empty(request('users'))){
            return parent::return_error("Missing Users", 401, 'messages.missing_parameter');
        }

        if(empty(request('subject'))){
            return parent::return_error("Missing Subject", 401, 'messages.missing_parameter');
        }

        if(empty(request('text'))){
            return parent::return_error("Missing Text", 401, 'messages.missing_parameter');
        }

        $player_ids = [];
    	$data = [];
    	$info = [];
        if($request->lang == 'ar'){
            $data['lang'] = 'ar';
        } else {
            $data['lang'] = 'en';
        }
    	$code = Controller::number_generator(6);





        $user_db = [];
        $users = explode(', ', $request->users);
        
        foreach($users as $user){            
            // Check for user
            $user_exists = AppUsers::select(['id'])->where('employee_id', $user)->first();

            if(!$user_exists){
                return parent::return_error("User not exists", 401, 'User Does not exist');
            }           
            $user_db[] = $user_exists->id;
        }

        /**
         * Get All Player IDs
         */
        $users = UserPush::select('users_id','player_id')->whereIn('users_id', $user_db)->get();

        if(count($users) > 0) {
        	foreach ($users as $player){
        		$player_ids[] = $player->player_id;
        	}
        }

        if(!empty($player_ids)){

        	$info['title'] = trim(request()->subject);
        	$info['body'] = trim(request()->text);
        	$info['data'] = $data;
        	$info['tokens'] = $player_ids;

        	$result = $this->sendPush($info);
        	if(!empty($result['error'])){
                return parent::return_error("error_msg", 401, $result['error']);
        	}else{
        		$status = $result['status'];
        		$message = $result['message'];
        		$debugger = $result['debugger'];
        		if($status == 'OK'){
                    return response()->json(['success' => $debugger]);
        		}else{
        			if($status == 'OK_WITH_ERRORS'){
                        return response()->json(['success' => $debugger]);
        			}else{
                        return response()->json(['success' => $debugger]);
        			}
        		}
        	}
        }else{
            return response()->json(['message' => 'No Users Found']);
        }

	}

}
