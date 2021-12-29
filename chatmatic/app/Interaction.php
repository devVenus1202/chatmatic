<?php

namespace App;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;


/**
 * App\Interaction
 *
 * @property int $uid
 * @property string $direction
 * @property int $subscriber_psid
 * @property int|null $workflow_uid
 * @property int|null $workflow_step_uid
 * @property int|null $campaign_uid
 * @property string|null $type
 * @property \Illuminate\Support\Carbon|null $created_at_utc
 * @property string|null $text_in
 * @property-read \App\Campaign|null $campaign
 * @property-read \App\Page $page
 * @property-read \App\Subscriber $subscriber
 * @property-read \App\Workflow|null $workflow
 * @property-read \App\WorkflowStep|null $workflowStep
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Interaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Interaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Interaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Interaction whereCampaignUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Interaction whereCreatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Interaction whereDirection($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Interaction whereSubscriberPsid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Interaction whereTextIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Interaction whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Interaction whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Interaction whereWorkflowStepUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Interaction whereWorkflowUid($value)
 * @mixin \Eloquent
 */

class Interaction extends Model
{
    const CREATED_AT        = 'created_at_utc';
    const UPDATED_AT        = null;

    protected $table        = 'interactions';
    protected $primaryKey   = 'uid';
    protected $guarded      = ['uid'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function page()
    {
        return $this->belongsTo(Page::class, 'page_uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflow()
    {
        return $this->belongsTo(Workflow::class, 'workflow_uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflowStep()
    {
        return $this->belongsTo(WorkflowStep::class, 'workflow_step_uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'campaign_uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subscriber()
    {
        return $this->belongsTo(Subscriber::class, 'subscriber_psid', 'user_psid');
    }

}
