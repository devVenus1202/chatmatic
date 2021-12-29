<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


/**
 * App\AuthTicket
 *
 * @property int $uid
 * @property int $chatmatic_user_uid
 * @property string $ip_address
 * @property string $token
 * @property string $created_at_utc
 * @property string $updated_at_utc
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AuthTicket whereChatmaticUserUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AuthTicket whereCreatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AuthTicket whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AuthTicket whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AuthTicket whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AuthTicket whereUpdatedAtUtc($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AuthTicket newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AuthTicket newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AuthTicket query()
 */
class AuthTicket extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $table = 'auth_ticket';
    protected $primaryKey = 'uid';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'chatmatic_user_uid', 'uid');
    }
}
