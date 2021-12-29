<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


/**
 * App\AutomationExecution
 *
 * @property-read \App\Automation $automation
 * @property-read \App\Page $page
 * @property-read \App\Subscriber $subscriber
 * @property-read \App\Workflow $workflow
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AutomationExecution newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AutomationExecution newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AutomationExecution query()
 * @mixin \Eloquent
 * @property int $uid
 * @property int $page_uid
 * @property int $automation_uid
 * @property int $workflow_uid
 * @property int $subscriber_uid
 * @property \Illuminate\Support\Carbon $created_at_utc
 * @property \Illuminate\Support\Carbon $updated_at_utc
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AutomationExecution whereAutomationUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AutomationExecution whereCreatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AutomationExecution wherePageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AutomationExecution whereSubscriberUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AutomationExecution whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AutomationExecution whereUpdatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AutomationExecution whereWorkflowUid($value)
 */
class AutomationExecution extends Model
{
    const CREATED_AT        = 'created_at_utc';
    const UPDATED_AT        = 'updated_at_utc';

    protected $table        = 'automation_executions';
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function automation()
    {
        return $this->belongsTo(Automation::class, 'automation_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflow()
    {
        return $this->belongsTo(Workflow::class, 'workflow_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subscriber()
    {
        return $this->belongsTo(Subscriber::class, 'subscriber_uid', 'uid');
    }
}
