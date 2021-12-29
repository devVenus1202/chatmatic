<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\WorkflowTemplateStepItemMap
 *
 * @property int $uid
 * @property int $workflow_template_step_item_uid
 * @property int $map_order
 * @property string $map_text
 * @property string $map_action
 * @property string|null $map_action_text
 * @property int $workflow_template_uid
 * @property string $type
 * @property-read \App\WorkflowTemplate $workflowTemplate
 * @property-read \App\WorkflowTemplateStepItem $workflowTemplateStepItem
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemMap whereMapAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemMap whereMapActionText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemMap whereMapOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemMap whereMapText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemMap whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemMap whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemMap whereWorkflowTemplateStepItemUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemMap whereWorkflowTemplateUid($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemMap newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemMap newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemMap query()
 * @property int|null $workflow_template_step_item_image_uid
 * @property-read \App\WorkflowStepItemImage|null $workflowTemplateStepItemImage
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemMap whereWorkflowTemplateStepItemImageUid($value)
 * @property-read \App\CustomFieldTemplate $customField
 */
class WorkflowTemplateStepItemMap extends Model
{
    protected $table        = 'workflow_template_step_item_map';
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
    public function workflowTemplateStepItem()
    {
        return $this->belongsTo(WorkflowTemplateStepItem::class, 'workflow_template_step_item_uid', 'uid');
    }

    /**
     * This is only in the case of carousel StepItemType's - where each StepItemImage associated with the StepItem is a carousel pane.
     * So buttons that are associated with those images are the buttons for that specific pane in the carousel.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflowTemplateStepItemImage()
    {
        return $this->belongsTo(WorkflowStepItemImage::class, 'workflow_template_step_item_image_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customField()
    {
        return $this->belongsTo(CustomFieldTemplate::class, 'custom_field_template_uid', 'uid');
    }

    /**
     * @param WorkflowStepItemMap $workflowStepItemMap
     * @param WorkflowTemplate $workflowTemplate
     * @param WorkflowTemplateStepItem $workflowTemplateStepItem
     * @param $workflowStepsArray
     * @param WorkflowTemplateStepItemImage|null $workflowTemplateStepItemImage
     * @return WorkflowTemplateStepItemMap|array|Model
     */
    public static function createFromWorkflowStepItemMap(
        WorkflowStepItemMap $workflowStepItemMap,
        WorkflowTemplate $workflowTemplate,
        WorkflowTemplateStepItem $workflowTemplateStepItem,
        $workflowStepsArray,
        WorkflowTemplateStepItemImage $workflowTemplateStepItemImage = null
    ) {
        $map_action_text = $workflowStepItemMap->map_action_text;
        // If the map_action_text contains a payload for a "next-step" we'll need to parse the map_action_text
        // for "next-step::1234", replacing 1234 with the correlating step uid
        if(mb_stristr($workflowStepItemMap->map_action_text, 'next-step'))
        {
            // Extract the uid of the next workflowStep from the button's map_action_text
            $nextStepUid    = explode('::', $map_action_text);
            $nextStepUid    = $nextStepUid[1];
            // Use that uid to grab the template step that correlates with the origin workflow step from our array
            if( ! isset($workflowStepsArray[$nextStepUid]['template']))
            {
                $response['error']      = 1;
                $response['error_msg']  = 'You cannot make a template out of a workflow that triggers other existing workflows. Change that step and try again. (Button: '.$workflowStepItemMap->map_text.')';

                return $response;
            }
            $nextStep       = $workflowStepsArray[$nextStepUid]['template'];
            // Rebuild the map_action_text to use this template step uid instead
            $map_action_text= 'next-step::'.$nextStep->uid;
        }

        $workflowTemplateStepItemMap = [
            'workflow_template_step_item_uid'       => $workflowTemplateStepItem->uid,
            'workflow_template_uid'                 => $workflowTemplate->uid,
            'map_order'                             => $workflowStepItemMap->map_order,
            'map_text'                              => $workflowStepItemMap->map_text,
            'map_action'                            => $workflowStepItemMap->map_action,
            'map_action_text'                       => $map_action_text,
            'workflow_template_step_item_image_uid' => null,
        ];

        // If an image was passed we're adding a button to a carousel pane (represented by the StepItemImage) and we want to associate this button to that
        if($workflowTemplateStepItemImage !== null)
        {
            $workflowTemplateStepItemMap['workflow_template_step_item_image_uid'] = $workflowTemplateStepItemImage->uid;
        }

        $workflowTemplateStepItemMap = \App\WorkflowTemplateStepItemMap::create($workflowTemplateStepItemMap);

        return $workflowTemplateStepItemMap;
    }

    /**
     * Generate an array that represents this button for the front end
     *
     * @return array
     */
    public function generateButtonArrayForFrontend()
    {
        // Initial mapping of button attributes to their front end representation
        $button = [
            'uid'               => $this->uid,
            'label'             => $this->map_text,
            'order'             => $this->map_order
        ];

        // Each type of button has a unique key where the value of it's action is expected, so we'll set those here
        switch($this->map_action)
        {
            case 'web_url':
                $button['openUrl']      = $this->map_action_text;
                $button['action_type']  = 'web_url';
                break;

            case 'postback':
                $step_uid = str_ireplace('next-step::', '', $this->map_action_text);
                $button['next_step_uid']    = (int) $step_uid;
                $button['action_type']      = 'postback';
                break;

            case 'phone_number':
                $button['phone']        = $this->map_action_text;
                $button['action_type']  = 'phone_number';
                break;

            case 'share':
                $button['action_type']  = 'share';
                break;
        }

        return $button;
    }
}
