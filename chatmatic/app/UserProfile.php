<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\UserProfile
 * 
 * @property int $uid
 * @property int $user_uid
 * @property string $description
 * @property string facebook_url
 * @property string twitter_url
 * @property string linkedin_url
 * @property string youtube_url
 * @property string other_url
 */

class UserProfile extends Model
{
    //
    public $timestamps      = false;
    protected $table        = 'chatmatic_user_profiles';
    protected $primaryKey   = 'uid';
    
    protected $guarded      = ['uid'];

    public function user() {
        return $this->belongsTo(User::class, 'chatmatic_user_uid', 'uid');
    }
}
