<?php

namespace App;

use App\Chatmatic\APIHelpers\FacebookGraphAPIHelper;
use App\Traits\WorkflowStepItemMediaTrait;
use Illuminate\Database\Eloquent\Model;


/**
 * App\WorkflowStepItemVideo
 *
 * @property int $uid
 * @property int $workflow_step_item_uid
 * @property string $video_url
 * @property int $page_uid
 * @property int $workflow_uid
 * @property int $workflow_step_uid
 * @property-read \App\Page $page
 * @property-read \App\Workflow $workflow
 * @property-read \App\WorkflowStep $workflowStep
 * @property-read \App\WorkflowStepItem $workflowStepItem
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemVideo wherePageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemVideo whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemVideo whereVideoUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemVideo whereWorkflowStepItemUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemVideo whereWorkflowStepUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemVideo whereWorkflowUid($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemVideo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemVideo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemVideo query()
 * @property string|null $reference_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemVideo whereReferenceId($value)
 * @property string|null $fb_attachment_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemVideo whereFbAttachmentId($value)
 */
class WorkflowStepItemVideo extends Model
{
    use WorkflowStepItemMediaTrait;

    protected $table        = 'workflow_step_item_videos';
    protected $primaryKey   = 'uid';

    public $timestamps      = false;

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
    public function workflow()
    {
        return $this->belongsTo(Workflow::class, 'workflow_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflowStep()
    {
        return $this->belongsTo(WorkflowStep::class, 'workflow_step_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflowStepItem()
    {
        return $this->belongsTo(WorkflowStepItem::class, 'workflow_step_item_uid', 'uid');
    }

    /**
     * @return bool
     */
    public function getFacebookAttachmentId()
    {
        $fb_helper = new FacebookGraphAPIHelper(
            config('chatmatic.app_id'),
            config('chatmatic.app_secret')
        );

        $response       = $fb_helper->getMediaObjectAttachmentId($this->page->facebook_connected_access_token, $this->video_url, 'video');
        $attachment_id  = $response['attachment_id'];

        $this->fb_attachment_id = $attachment_id;
        return $this->save();
    }
}
