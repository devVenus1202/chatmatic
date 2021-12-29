<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


/**
 * App\AuditLog
 *
 * @property int $uid
 * @property int|null $chatmatic_user_uid
 * @property int|null $page_uid
 * @property string $event
 * @property string $message
 * @property \Illuminate\Support\Carbon $created_at_utc
 * @property \Illuminate\Support\Carbon $updated_at_utc
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AuditLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AuditLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AuditLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AuditLog whereChatmaticUserUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AuditLog whereCreatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AuditLog whereEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AuditLog whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AuditLog wherePageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AuditLog whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AuditLog whereUpdatedAtUtc($value)
 * @mixin \Eloquent
 */
class AuditLog extends Model
{
    const CREATED_AT        = 'created_at_utc';
    const UPDATED_AT        = 'updated_at_utc';

    protected $table        = 'audit_log';
    protected $primaryKey   = 'uid';

    protected $guarded      = ['uid'];
}
