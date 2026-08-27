<?php

namespace App\Http\Controllers\Voyager;

use Illuminate\Http\Request;
use App\Models\Api\UserPush;
use App\Models\Api\Inbox;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PushController extends VoyagerBreadController
{

	public function index()
	{

		$app_users = UserPush::whereHas('user')->get();

		return view('cms.push-notifications', compact(
			'app_users'
		));
	}

	// ALL USERS NOTOFICATIONS
	public function send_push(Request $request)
	{
		$this->validate($request, [
			'title' => 'required',
			'message' => 'required'
		]);

		$player_ids = [];
		$included_segments = [];
        /**
         * excluded will not received even if they are in included
         */
        $excluded_segments = [];
        $data = [];
        $info = [];
        if($request->lang == 'ar'){
            $data['lang'] = 'ar';
        } else {
            $data['lang'] = 'en';
        }
        $code = Controller::number_generator(6);

        // $destinationDir = public_path() . DIRECTORY_SEPARATOR . 'uploads/images' . DIRECTORY_SEPARATOR . 'push';
        // $fileName = null;
        // check if a file exist and is valid.
        if($request->hasFile('image')) {
        	if($request->file('image')->isValid()) {
                $file = $request->file('image');

                $fileName = basename($file->getClientOriginalName(), '.'.$file->getClientOriginalExtension());
                $fileName = str_replace(" ", "-", $fileName);
                $fileExtension = $file->getClientOriginalExtension();
                $originalName = $fileName . "-" . rand() . "." . $fileExtension;
                $image = $file->storeAs('public/push-notifications', $originalName);
                $image_path = Storage::url($image);
                $image_path = asset($image_path);

                /**
                 * this image will be displayed in the notification
                 */
                //Android
                $info['big_picture'] = $image_path;
                //iOS
                $media_id = 'id'.Str::random(5);
                $info['ios_attachments'] = [$media_id => $image_path];
                /**
                 * at the same time, we will send the image in "data" in case
                 * they want to display it int the application
                 */
                // list($width, $height, $type, $attr) = getimagesize(url('/').$image_path);
                // $data['image_width'] = $width;
                // $data['image_height'] = $height;
                $data['image_url'] = $image_path;
            }
        }

        if($request->segments){// could be "All" "Active Users" "Inactive Users"
        $included_segments = [$request->segments];
            /**
             * excluded segments is not implemented yet
             */
        }else{
            /**
             * Get All Player IDs
             */
            $users = UserPush::select('player_id')->where('player_id', '<>', '')->get();

            // /**
            //  * if targeted then send the options in "info" key
            //  */
            // if($request->info){
            //     /**
            //      * check example below
            //      */
            //     $types = $request->info['types'];
            //     if(in_array('all',$types)){
            //     	$users = Users::join('user_push','users_id','=','users.id')
            //     	->select('user_push.users_id','user_push.player_id')
            //     	->distinct()
            //     	->get();
            //     }else{
            //     	$users = Users::join('user_push','users_id','=','users.id')
            //     	->whereIn('department',$types)
            //     	->select('user_push.users_id','user_push.player_id')
            //     	->distinct()
            //     	->get();
            //     }
            // }

            if(count($users) > 0) {
            	foreach ($users as $player){
            		$player_ids[] = $player->player_id;
            	}
            }
            if(empty($player_ids)){
            	return redirect()->back()
            	->with('warning', 'No Users Found');
            }
        }

        $headings = ["en" => trim(request()->title)];
        $contents = ["en" => trim(request()->message)];
        //extra data to send to application

        // if(isset($request->info['data'])){
        // 	$data = array_push($data,$request->info['data']);
        // }

        $info['contents'] = $contents;
        $info['headings'] = $headings;
        $info['data'] = $data;
        $info['player_ids'] = $player_ids;
        $info['included_segments'] = $included_segments;
        $info['excluded_segments'] = $excluded_segments;

        $result = $this->oneSignal($info);
        if(!empty($result['error'])){
        	return redirect()->back()
        	->with('error_msg', $result['error']);
        }else{
        	$status = $result['status'];
        	$message = $result['message'];
        	$debugger = $result['debugger'];
        	if($status == 'OK'){
        		Inbox::create([
        			'user_id' => null,
        			'title' => trim(request()->title),
        			'message' => trim(request()->message),
        			'image' => isset($image_path) && $image_path ? $image_path : null,
        			'type' => 'bulk',
        			'code' => $code,
                    'lang' => $data['lang']
        		]);
        		return redirect()->back()
        		->with('success', $debugger);
        	}else{
        		if($status == 'OK_WITH_ERRORS'){
        			return redirect()->back()
        			->with('warning', $debugger);
        		}else{
        			return redirect()->back()
        			->with('error_msg', $debugger);
        		}
        	}
        }

    }

    public function send_single_push(Request $request)
    {
    	$this->validate($request, [
    		'users' => 'required',
    		'title' => 'required',
    		'message' => 'required'
    	]);

    	$player_ids = [];
    	$data = [];
    	$info = [];
        if($request->lang == 'ar'){
            $data['lang'] = 'ar';
        } else {
            $data['lang'] = 'en';
        }
    	$code = Controller::number_generator(6);

        // check if a file exist and is valid.
        if($request->hasFile('image')) {
            if($request->file('image')->isValid()) {
                $file = $request->file('image');

                $fileName = basename($file->getClientOriginalName(), '.'.$file->getClientOriginalExtension());
                $fileName = str_replace(" ", "-", $fileName);
                $fileExtension = $file->getClientOriginalExtension();
                $originalName = $fileName . "-" . rand() . "." . $fileExtension;
                $image = $file->storeAs('public/push-notifications', $originalName);
                $image_path = Storage::url($image);
                $image_path = asset($image_path);

                /**
                 * this image will be displayed in the notification
                 */
                //Android
                $info['big_picture'] = $image_path;
                //iOS
                $media_id = 'id'.Str::random(5);
                $info['ios_attachments'] = [$media_id => $image_path];
                /**
                 * at the same time, we will send the image in "data" in case
                 * they want to display it int the application
                 */
                // list($width, $height, $type, $attr) = getimagesize(url('/').$image_path);
                // $data['image_width'] = $width;
                // $data['image_height'] = $height;
                $data['image_url'] = asset($image_path);
            }
        }

        /**
         * if we want to send badge
         * there are 2 types
         * The first option if we want to set a customized number in the badge
         * the second option if we want to increase/decrease  by "x" number every time we send a push
         */
        if($request->badge){
        	if($request->badge == 'manual'){
        		$info['ios_badgeType'] = 'SetTo';
                $info['ios_badgeCount'] = '10';// you can set the number you want
            }elseif ($request->badge == 'auto'){
            	$info['ios_badgeType'] = 'Increase';
                $info['ios_badgeCount'] = '10';// if number negative it will decrease
            }
        }else{// by default we will follow the default behavior which is increasing the badge by one each time we send a push
        	$info['ios_badgeType'] = 'Increase';
        	$info['ios_badgeCount'] = '1';
        }

        // /**
        //  * if targeted then send the options in "info" key
        //  */
        // if($request->info){
        //     /**
        //      * check example below
        //      */
        //     $types = $request->info['types'];
        //     if(in_array('all',$types)){
        //     	$users = Users::join('user_push','users_id','=','users.id')
        //     	->select('user_push.users_id','user_push.player_id')
        //     	->distinct()
        //     	->get();
        //     }else{
        //     	$users = Users::join('user_push','users_id','=','users.id')
        //     	->whereIn('department',$types)
        //     	->select('user_push.users_id','user_push.player_id')
        //     	->distinct()
        //     	->get();
        //     }
        // }

        /**
         * Get All Player IDs
         */
        $users = UserPush::select('users_id','player_id')->whereIn('users_id', $request['users'])->get();

        if(count($users) > 0) {
        	foreach ($users as $player){
        		Inbox::create([
        			'user_id' => $player->users_id,
        			'title' => trim(request()->title),
        			'message' => trim(request()->message),
        			'image' => isset($image_path) && $image_path ? $image_path : null,
        			'type' => 'targeted',
        			'code' => $code,
                    'lang' => $data['lang']
        		]);
        		$player_ids[] = $player->player_id;
        	}
        }
        if(!empty($player_ids)){

        	$headings = ["en" => trim(request()->title)];
        	$contents = ["en" => trim(request()->message)];
            //extra data to send to application

        	// if($request->info['data']){
        	// 	$data = array_push($data,$request->info['data']);
        	// }

        	$info['contents'] = $contents;
        	$info['headings'] = $headings;
        	$info['data'] = $data;
        	$info['player_ids'] = $player_ids;

        	$result = $this->oneSignal($info);
        	if(!empty($result['error'])){
        		return redirect()->back()
        		->with('error_msg', $result['error']);
        	}else{
        		$status = $result['status'];
        		$message = $result['message'];
        		$debugger = $result['debugger'];
        		if($status == 'OK'){
        			return redirect()->back()
        			->with('success', $debugger);
        		}else{
        			if($status == 'OK_WITH_ERRORS'){
        				return redirect()->back()
        				->with('warning', $debugger);
        			}else{
        				return redirect()->back()
        				->with('error_msg', $debugger);
        			}
        		}
        	}
        }else{
        	return redirect()->back()
        	->with('warning', 'No Users Found');
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
