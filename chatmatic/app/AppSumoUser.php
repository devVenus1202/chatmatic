<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


/**
 * App\ApiErrorLog
 *
 * @property int $uid
 * @property string $error_msg
 */
class AppSumoUser extends Model
{
    public $timestamps      = false;
    
    const CREATED_AT        = 'created_at_utc';
    const UPDATED_AT        = 'updated_at_utc';

    protected $table        = 'app_sumo_users';
    protected $primaryKey   = 'uid';

    protected $guarded      = ['uid'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function chatmaticUser()
    {
        return $this->belongsTo(User::class, 'chatmatic_user_id', 'uid');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sumoLicensedPages()
    {
        return $this->hasMany(PageLicense::class, 'appsumo_user_uid', 'uid');
    }
    



}
