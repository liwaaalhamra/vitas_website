<?php

namespace App\Models\Api;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Api\UserPoints
 *
 * @property integer $id
 * @property integer $users_id
 * @property string $meta_name
 * @property string $image
 * @property double $points
 * @property string $description
 * @property integer $meta_value
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Api\UserPoints whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Api\UserPoints whereImage($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Api\UserPoints whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Api\UserPoints whereUsersId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Api\UserPoints wherePoints($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Api\UserPoints whereMetaName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Api\UserPoints whereMetaValue($value)
 * @mixin \Eloquent
 */
class AppUsersPoint extends Model
{
    protected $table = "app_users_points";
    protected $guarded = [];
    protected $hidden = ['updated_at','users_id','meta_value'];
    protected $appends = ['from'];

    // public function getFromAttribute(){
    //     if($this->attributes['meta_name'] == 'gifts'){
    //         return 'From Gifts Section';
    //     }elseif ($this->attributes['meta_name'] == 'redeem'){
    //         return 'Redeem';
    //     }elseif ($this->attributes['meta_name'] == 'system'){
    //         $cms_user = User::whereId($this->attributes['meta_value'])->first();
    //         if($cms_user){
    //             return 'From '.$cms_user->first_name.' '.$cms_user->last_name;
    //         }else{
    //             return 'From Hr';
    //         }
    //     }
    // }

    // public function getItemDatetimeAttribute($value)
    // {
    //     return $value == null ? null : \Carbon\Carbon::parse($value)->getTimestamp();
    // }

    // public function setItemDatetimeAttribute($item_datetime)
    // {
    //     $this->attributes['item_datetime'] = $item_datetime == null ? null : Carbon::createFromTimestamp($item_datetime)->format('Y-m-d H:i:s');
    // }

    public function user(){
        return $this->belongsTo(AppUsers::class, 'users_id', 'id');
    }

    // public function getCreatedAtAttribute($value)
    // {
    //     return \Carbon\Carbon::parse($value)->getTimestamp();
    // }

    // public function getImageAttribute($value){
    //     return ($value == null) ? null : secure_url('public/uploads/images/selfies/'.$value);
    // }
}
