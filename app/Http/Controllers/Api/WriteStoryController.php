<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Api\AppUsers;
use App\Models\Api\AppUsersPoint;
use App\Story;
use Storage;

class WriteStoryController extends Controller
{

	public function index(Request $request)
	{
		if($access_token = parent::valid_access_token($request)) {
			if($user = parent::get_user_by_access_token($access_token)){

				if(empty(request('image'))){
					return parent::return_error("Missing Image", 401, 'messages.missing_parameter');
				}
				if(empty(request('full_name'))){
					return parent::return_error("Missing Full Name", 401, 'messages.missing_parameter');
				}
				if(empty(request('story_title'))){
					return parent::return_error("Missing Story Title", 401, 'messages.missing_parameter');
				}
				if(empty(request('story_text'))){
					return parent::return_error("Missing Story Text", 401, 'messages.missing_parameter');
				}

				$file = $request->image;
				$fileName = basename($file->getClientOriginalName(), '.'.$file->getClientOriginalExtension());
				$fileExtension = $file->getClientOriginalExtension();

				$originalName = $fileName . "-" . rand() . "." . $fileExtension;
				$image = $file->storeAs('public/write-my-story', $originalName);
				$image_path = url('/') . Storage::url($image);

				$story = new Story;
				$story->image = $image_path;
				$story->full_name = $request->full_name;
				$story->title = $request->story_title;
				$story->text = $request->story_text;
				$story->save();

				$now = Carbon::now()->format('Y');
				if(!$user_points = AppUsersPoint::whereYear('created_at', $now)->where([['user_id', $user->employee_id], ['meta_name', 'write_success_story']])->first()){
					$user_points = new AppUsersPoint;
					$user_points->user_id = $user->employee_id;
					$user_points->meta_name = "write_success_story";
					$user_points->points = 75;
					$user_points->description = "Write success story 1/year";
					$user_points->save();

					if($user_total_points = AppUsers::where('employee_id', $user->employee_id)->first()){
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

}
