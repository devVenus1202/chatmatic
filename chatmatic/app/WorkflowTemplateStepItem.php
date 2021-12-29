<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\WorkflowTemplateStepItem
 *
 * @property int $uid
 * @property int $workflow_template_step_uid
 * @property string $item_type
 * @property string $headline
 * @property string $content
 * @property int $workflow_template_uid
 * @property string $text_message
 * @property-read \App\WorkflowTemplate $workflowTemplate
 * @property-read \App\WorkflowTemplateStep $workflowTemplateStep
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\WorkflowTemplateStepItemImage[] $workflowTemplateStepItemImages
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\WorkflowTemplateStepItemMap[] $workflowTemplateStepItemMaps
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItem whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItem whereHeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItem whereItemType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItem whereTextMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItem whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItem whereWorkflowTemplateStepUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItem whereWorkflowTemplateUid($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItem query()
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\WorkflowTemplateStepItemAudio[] $workflowTemplateStepItemAudios
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\WorkflowTemplateStepItemVideo[] $workflowTemplateStepItemVideos
 * @property int|null $item_order
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItem whereItemOrder($value)
 * @property-read int|null $workflow_template_step_item_audios_count
 * @property-read int|null $workflow_template_step_item_images_count
 * @property-read int|null $workflow_template_step_item_maps_count
 * @property-read int|null $workflow_template_step_item_videos_count
 */
class WorkflowTemplateStepItem extends Model
{
    protected $table        = 'workflow_template_step_items';
    protected $primaryKey   = 'uid';
    protected $guarded      = ['uid'];

    public $timestamps      = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflowTemplate()
    {
        return $this->belongsTo(WorkflowTemplate::class, 'workflow_template_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflowTemplateStep()
    {
        return $this->belongsTo(WorkflowTemplateStep::class, 'workflow_template_step_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workflowTemplateStepItemMaps()
    {
        return $this->hasMany(WorkflowTemplateStepItemMap::class, 'workflow_template_step_item_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workflowTemplateStepItemImages()
    {
        return $this->hasMany(WorkflowTemplateStepItemImage::class, 'workflow_template_step_item_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workflowTemplateStepItemAudios()
    {
        return $this->hasMany(WorkflowTemplateStepItemAudio::class, 'workflow_template_step_item_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workflowTemplateStepItemVideos()
    {
        return $this->hasMany(WorkflowTemplateStepItemVideo::class, 'workflow_template_step_item_uid', 'uid');
    }

    /**
     * @param WorkflowTemplateStepItem $workflowTemplateStepItem
     * @param Workflow $workflow
     * @param WorkflowStep $workflowStep
     * @return array
     */
    public static function prepareForStepItem(WorkflowTemplateStepItem $workflowTemplateStepItem, Workflow $workflow, WorkflowStep $workflowStep)
    {
        $workflowStepItem = $workflowTemplateStepItem->toArray();

        // Unset the stuff we don't need/will replace
        unset($workflowStepItem['uid']);
        unset($workflowStepItem['workflow_template_step_uid']);
        unset($workflowStepItem['workflow_template_uid']);

        // Set the stuff we need
        $workflowStepItem['workflow_step_uid']  = $workflowStep->uid;
        $workflowStepItem['workflow_uid']       = $workflow->uid;
        $workflowStepItem['page_uid']           = $workflow->page_uid;

        // Return the array
        return $workflowStepItem;
    }
}
