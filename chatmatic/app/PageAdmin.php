<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


/**
 * App\PageAdmin
 *
 * @property int $uid
 * @property int $page_uid
 * @property int $user_uid
 * @property int $added_by
 * @property bool $deleted
 * @property \Illuminate\Support\Carbon $created_at_utc
 * @property \Illuminate\Support\Carbon $updated_at_utc
 * @property-read \App\User $addedBy
 * @property-read \App\Page $page
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageAdmin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageAdmin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageAdmin query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageAdmin whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageAdmin whereCreatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageAdmin whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageAdmin wherePageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageAdmin whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageAdmin whereUpdatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageAdmin whereUserUid($value)
 * @mixin \Eloquent
 * @property string $email
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageAdmin whereEmail($value)
 */
class PageAdmin extends Model
{
    const CREATED_AT        = 'created_at_utc';
    const UPDATED_AT        = 'updated_at_utc';

    protected $table        = 'page_admins';
    protected $primaryKey   = 'uid';

    protected $guarded      = ['uid'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function page()
    {
        return $this->belongsTo(Page::class, 'page_uid', 'uid');
    }
}
