<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Automation
 *
 * @property-read \App\Page $page
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Automation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Automation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Automation query()
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\AutomationExecution[] $executions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Tag[] $tags
 * @property int $uid
 * @property int $page_uid
 * @property string $name
 * @property bool $active
 * @property bool $user_unsubscribe
 * @property \Illuminate\Support\Carbon $created_at_utc
 * @property \Illuminate\Support\Carbon $updated_at_utc
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Automation whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Automation whereCreatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Automation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Automation wherePageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Automation whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Automation whereUpdatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Automation whereUserUnsubscribe($value)
 * @property bool $notification
 * @property string|null $message_content
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Automation whereMessageContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Automation whereNotification($value)
 * @property string $trigger_integrations
 * @property int|null $integration_uid
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Automation whereIntegrationUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Automation whereTriggerIntegrations($value)
 * @property-read int|null $executions_count
 * @property-read int|null $tags_count
 */
class Automation extends Model
{
    const CREATED_AT        = 'created_at_utc';
    const UPDATED_AT        = 'updated_at_utc';

    protected $table        = 'automations';
    protected $primaryKey   = 'uid';

    protected $guarded      = ['uid'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function page()
    {
        return $this->belongsTo(Page::class, 'page_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable', 'taggables', 'taggable_uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function executions()
    {
        return $this->hasMany(AutomationExecution::class,'automation_uid', 'uid');
    }
}
