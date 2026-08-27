<?php

namespace App\Models\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Storage;

/**
 * App\Models\Api\Inbox
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $is_read
 * @property string $code
 * @property string $type
 * @property string $image
 * @property string $title
 * @property string $message
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Api\Inbox whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Api\Inbox whereIsRead($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Api\Inbox whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Api\Inbox whereCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Api\Inbox whereType($value)
 * @mixin \Eloquent
 */
class Inbox extends Model
{
    protected $table = 'push_inbox';
    protected $guarded = [];
    protected $hidden = ['updated_at','code','type','user_id','is_read'];

    // public function getCreatedAtAttribute($value)
    // {
    //     return \Carbon\Carbon::parse($value)->getTimestamp();
    // }

    public function user(){
        return $this->belongsTo(AppUsers::class,'user_id');
    }

    // public function getImageAttribute($value){
    //     return ($value == null) ? null : url('/') . Storage::url('inbox/' . $value);
    // }

    // public function getImageName(){
    //     return $this->attributes['image'];
    // }
}
