<?php

namespace App\Http\Controllers\Voyager;

use Illuminate\Http\Request;
use App\Models\Api\UserPush;
use App\Models\Api\Inbox;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Services\FirebaseService;

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
                $info['image'] = $image_path;
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

        $info['title'] = trim(request()->title);
        $info['body'] = trim(request()->message);
        //extra data to send to application
        $info['data'] = $data;
        /**
         * Broadcast to the global language topic, every app install (logged in or not)
         * is subscribed to the one matching its current language
         */
        $info['topic'] = $data['lang'] == 'ar' ? FirebaseService::TOPIC_AR : FirebaseService::TOPIC_EN;

        $result = $this->sendPush($info);
        if(!empty($result['error'])){
        	return redirect()->back()
        	->with('error_msg', $result['error']);
        }else{
        	$status = $result['status'];
        	$message = $result['message'];
        	$debugger = $result['debugger'];
        	// some devices failing (e.g. uninstalled apps) should not keep the message out of the inbox
        	Inbox::create([
        		'user_id' => null,
        		'title' => trim(request()->title),
        		'message' => trim(request()->message),
        		'image' => isset($image_path) && $image_path ? $image_path : null,
        		'type' => 'bulk',
        		'code' => $code,
                'lang' => $data['lang']
        	]);
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
                $info['image'] = $image_path;
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

        	$info['title'] = trim(request()->title);
        	$info['body'] = trim(request()->message);
            //extra data to send to application
        	$info['data'] = $data;
        	$info['tokens'] = $player_ids;

        	$result = $this->sendPush($info);
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

    function sendPush($info){
    	try{
    		return app(FirebaseService::class)->send($info);
    	}catch (\Exception $ex){
    		return ['error' => 'Exception Error: '.$ex->getMessage().' - Failed to Send Push'];
    	}
    }

}
