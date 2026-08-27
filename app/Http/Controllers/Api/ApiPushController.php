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

        	$headings = ["en" => trim(request()->subject)];
        	$contents = ["en" => trim(request()->text)];

        	$info['contents'] = $contents;
        	$info['headings'] = $headings;
        	$info['data'] = $data;
        	$info['player_ids'] = $player_ids;

        	$result = $this->oneSignal($info);
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

    function oneSignal($info){
    	$contents = $info['contents'];
    	$headings = $info['headings'];
    	$player_ids = $info['player_ids'];
    	$included_segments = !isset($info['included_segments']) ? [] : $info['included_segments'];
    	$excluded_segments = !isset($info['excluded_segments']) ? [] : $info['excluded_segments'];
    	$data = $info['data'];
    	$result = [];

    	$fields = array(
    		'app_id' => env('ONE_SIGNAL_APP_ID'),
    		'include_player_ids' => $player_ids,
    		'included_segments' => $included_segments,
    		'excluded_segments' => $excluded_segments,
    		'contents' => $contents,
    		'headings' => $headings,
			// 'send_after' => Carbon::now('UTC')->addDays(1)->format('Y-m-d H:i:s'),
			// 'send_after' => Carbon::now('UTC')->addHours(2)->format('Y-m-d H:i:s'),
    	);
    	if(isset($info['send_after'])){
    		$fields['send_after'] = $info['send_after'];
    	}
    	if(!empty($info['data'])){
    		$fields['data'] = $info['data'];
    	}
    	if(isset($info['big_picture'])){
    		$fields['big_picture'] = $info['big_picture'];
    	}
    	if(isset($info['ios_attachments'])){
    		$fields['ios_attachments'] = $info['ios_attachments'];
    	}
    	if(isset($info['ios_badgeType'])){
    		$fields['ios_badgeType'] = $info['ios_badgeType'];
    	}
    	if(isset($info['ios_badgeCount'])){
    		$fields['ios_badgeCount'] = $info['ios_badgeCount'];
    	}
    	$fields = json_encode($fields);
    	try{
    		$ch = curl_init();
    		curl_setopt($ch, CURLOPT_URL, env('ONE_SIGNAL_URL'));
    		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
    			'Authorization: Basic '.env('ONE_SIGNAL_REST_API_KEY')));
    		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    		curl_setopt($ch, CURLOPT_HEADER, FALSE);
    		curl_setopt($ch, CURLOPT_POST, TRUE);
    		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    		$response = curl_exec($ch);
    		$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    		$err = curl_errno($ch);

    		curl_close($ch);
    		if ($err) {
    			$result['error'] = 'Curl Error: '.$err.' - Failed to Send Push';
    		} else {
    			$result = $this->parse_push_response($response);
    		}
    	}catch (\Exception $ex){
    		$result['error'] = 'Exception Error: '.$ex->getMessage().' - Failed to Send Push';
    	}
    	return $result;
    }

    public function parse_push_response($response){
    	$response = json_decode($response,true);
    	$response = (array) $response;
    	$data = [];
    	$data['status'] = '';
    	$data['message'] = '';
    	$data['debugger'] = '';
    	if(isset($response['id'])){
            if(!empty($response['id'])){//200 ok
            	$data['status'] = 'OK';
            	$data['message'] = 'Push Sent Successfully to '.$response['recipients'].' devices';
            	$data['debugger'] = 'Push Sent Successfully to '.$response['recipients'].' devices';
            }else{//200 no subscribed players
            	$data['status'] = 'OK_WITH_ERRORS';
            	if(isset($response['errors'])){
            		$data['message'] = isset($response['errors'][0]) ? $response['errors'][0] : 'All included players are not subscribed';
            		$data['debugger'] = isset($response['errors'][0]) ? $response['errors'][0] : 'All included players are not subscribed';
            	}
            }
        }else{

        	if(isset($response['errors'])){
                if(!empty($response['errors']['invalid_player_ids'])){//200 invalid player ids
                	$data['status'] = 'OK_WITH_ERRORS';
                	$data['message'] = 'Some Users are Invalid';
                	$data['debugger'] ='Some Users are Invalid';
                }else{//400
                	$data = [];
                	$data['error'] = isset($response['errors'][0]) ? $response['errors'][0] : 'Invalid Notification Content';
                }
            }else{//400
            	$data = [];
            	$data['error'] = 'Unknown Error';
            }
        }
        return $data;
    }

}
