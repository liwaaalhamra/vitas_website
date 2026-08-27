<?php

namespace App\Models\Api;

// use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
// use Hash;

/**
 * App\Models\Api\Users
 *
 * @mixin \Eloquent
 */
class AppUsers extends Model
{
	protected $table = 'app_users';
    //public $timestamps = false;
	protected $guarded = [];

    protected $hidden = ['first_name', 'english_name', 'customer_name', 'mobile_number', 'email', 'gender', 'quarter', 'town', 'governorate', 'total_points', 'registration', 'reset_code', 'updated_at', 'created_at'];

	/**
	 * A user may have a push token.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function pushToken()
	{
		return $this->hasOne(UserPush::class);
	}

    public function points()
    {
        return $this->hasMany(AppUsersPoint::class,'users_id','id');
    }

    /**
     * A user may has an access token.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function access_token()
    {
        return $this->hasOne(AppUsersAccess::class,'user_id','id');
    }

}
