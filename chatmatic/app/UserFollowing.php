<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserFollowing extends Model
{
    //
    protected $table="chatmatic_user_followings";
    protected $primaryKey = 'uid';

    protected $guarded = ['uid'];

    public function followee() {
        return $this->belongsTo(User::class, 'followee_uid', 'uid');
    }

    public function follower() {
        return $this->belongsTo(User::class, 'follower_uid', 'uid');
    }
}
