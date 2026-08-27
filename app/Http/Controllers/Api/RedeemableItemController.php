<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\RedeemableItem;
use App\Redeem;
use App\Models\Api\AppUsers;
use App;

class RedeemableItemController extends Controller
{

	public function index(Request $request)
	{
		// Get Redeemable Items
		$about = RedeemableItem::select([
			'id',
			'image',
			'points',
			'title_' . App::getLocale() . ' as title',
			'text_' . App::getLocale() . ' as text'
		])->orderBy('points', 'asc')->get();

		return response()->json($about);
	}

	public function redeem(Request $request)
	{
		if($access_token = parent::valid_access_token($request)) {
			if($user = parent::get_user_by_access_token($access_token)) {

				if(empty(request('redeemable_item_id'))){
					return parent::return_error("Missing Redeemable Item Id", 401, 'messages.missing_parameter');
				}

				if($user['can_redeem'] == 1){

					// The user can not redeem before getting approval
					$user['can_redeem'] = 0;
					$user->save();

					// Get Redeemable Item Id
					$item = RedeemableItem::select([
						'id',
						'points'
					])->findOrFail($request['redeemable_item_id']);

					// Points calculation
					if($item['points'] > $user->total_points){
						return parent::return_error("You don't have enough points!", 101, 'messages.not_enough_points');
					}
					if($item['points'] <= $user->total_points){
						$redeem = new Redeem;
						$redeem->user_id = $user->employee_id;
						$redeem->redeemable_item_id = $item['id'];
						$redeem->save();

						return response()->json(['status' => 'messages.redeemed_confirmation']);
					}

				} else {
					return parent::return_error("Pending previous redeem request", 101, 'messages.pending_redeemed');
				}

			} else {
				return parent::return_error("Invalid Access Token", 401, 'messages.invalid_access_token');
			}
		} else {
			return parent::return_error("Missing Access Token", 102, 'messages.authentication_missing');
		}
	}

}
