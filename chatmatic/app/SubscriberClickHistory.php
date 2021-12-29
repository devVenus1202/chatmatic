<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


/**
 * App\SubscriberClickHistory
 *
 */
class SubscriberClickHistory extends Model
{
    const CREATED_AT        = 'created_at_utc';
    const UPDATED_AT        = 'updated_at_utc';

    protected $table        = 'subscriber_click_history';
    protected $primaryKey   = 'uid';

    public function getDateFormat()
    {
        return 'Y-m-d H:i:s';
    }

    public function subscriber()
    {
        return $this->belongsTo(Subscriber::class, 'subscriber_uid', 'uid');
    }

    public function workflow()
    {
        return $this->belongsTo(Workflow::class, 'workflow_uid', 'uid');
    }

    public function workflowStep()
    {
        return $this->belongsTo(WorkflowStep::class, 'workflow_step_uid', 'uid');
    }
}
