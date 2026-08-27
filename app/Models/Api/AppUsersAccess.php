<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Api\UsersAccess
 *
 * @mixin \Eloquent
 * @property integer $id
 * @property integer $user_id
 * @property string $access_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Api\UsersAccess whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Api\UsersAccess whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Api\UsersAccess whereAccessToken($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Api\UsersAccess whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Api\UsersAccess whereUpdatedAt($value)
 */
class AppUsersAccess extends Model
{
	protected $table = 'app_users_access';

	protected $fillable = ['user_id','access_token'];

	protected $hidden = ["created_at", "updated_at"];

    public function user(){
        return $this->belongsTo(AppUsers::class,'user_id');
    }
}
