<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;
/**
 * App\Models\Api\UserPush
 *
 * @mixin \Eloquent
 * @property integer $id
 * @property integer $users_id
 * @property integer $player_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Api\UserPush whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Api\UserPush whereUsersId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Api\UserPush wherePlayerId($value)
 */
class UserPush extends Model
{
    protected $table = "app_user_push";
    protected $fillable = ['users_id','player_id'];

    public function user(){
        return $this->belongsTo(AppUsers::class,'users_id');
    }

}
