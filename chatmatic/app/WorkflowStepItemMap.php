<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\WorkflowStepItemMap
 *
 * @property int $uid
 * @property int $workflow_step_item_uid
 * @property int $map_order
 * @property string $map_text
 * @property string $map_action
 * @property string|null $map_action_text
 * @property int $page_uid
 * @property int $workflow_uid
 * @property int $workflow_step_uid
 * @property-read \App\Page $page
 * @property-read \App\Workflow $workflow
 * @property-read \App\WorkflowStep $workflowStep
 * @property-read \App\WorkflowStepItem $workflowStepItem
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemMap whereMapAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemMap whereMapActionText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemMap whereMapOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemMap whereMapText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemMap wherePageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemMap whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemMap whereWorkflowStepItemUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemMap whereWorkflowStepUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemMap whereWorkflowUid($value)
 * @mixin \Eloquent
 * @property string $type
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemMap whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemMap newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemMap newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemMap query()
 * @property int|null $automation_uid
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemMap whereAutomationUid($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Tag[] $tags
 * @property int|null $workflow_step_item_image_uid
 * @property-read \App\WorkflowStepItemImage|null $workflowStepItemImage
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemMap whereWorkflowStepItemImageUid($value)
 * @property int $clicks
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemMap whereClicks($value)
 * @property int|null $outbound_link_uid
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemMap whereOutboundLinkUid($value)
 * @property int|null $custom_field_uid
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemMap whereCustomFieldUid($value)
 * @property-read \App\CustomField|null $customField
 * @property-read int|null $tags_count
 */
class WorkflowStepItemMap extends Model
{
    protected $table        = 'workflow_step_item_map';
    protected $primaryKey   = 'uid';

    protected $guarded      = ['uid'];

    public $timestamps      = false;

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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customField()
    {
        return $this->belongsTo(CustomField::class, 'custom_field_uid', 'uid');
    }

    /**
     * This is only in the case of carousel StepItemType's - where each StepItemImage associated with the StepItem is a carousel pane.
     * So buttons that are associated with those images are the buttons for that specific pane in the carousel.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflowStepItemImage()
    {
        return $this->belongsTo(WorkflowStepItemImage::class, 'workflow_step_item_image_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable', 'taggables', 'taggable_uid');
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
            'tags'              => [],
            'automation_uid'    => $this->automation_uid,
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

        // Populate button tags
        foreach($this->tags()->get() as $tag)
        {
            /** @var \App\Tag $tag */

            $button['tags'][] = [
                'uid'   => $tag->uid,
                'value' => $tag->value
            ];
        }

        return $button;
    }

    /**
     * @param WorkflowTemplateStepItemMap $workflowTemplateStepItemMap
     * @param Workflow $workflow
     * @param WorkflowStepItem $workflowStepItem
     * @param $workflowTemplateStepsArray
     * @param WorkflowStepItemImage|null $workflowStepItemImage
     * @return WorkflowStepItemMap|array|Model
     */
    public static function createFromWorkflowTemplateStepItemMap(
        WorkflowTemplateStepItemMap $workflowTemplateStepItemMap,
        Workflow $workflow,
        WorkflowStepItem $workflowStepItem,
        $workflowTemplateStepsArray,
        WorkflowStepItemImage $workflowStepItemImage = null
    ) {
        $map_action_text = $workflowTemplateStepItemMap->map_action_text;
        // If the map_action_text contains a payload for a "next-step" we'll need to parse the map_action_text
        // for "next-step::1234", replacing 1234 with the correlating step uid
        if(mb_stristr($workflowTemplateStepItemMap->map_action_text, 'next-step'))
        {
            // Extract the uid of the next workflowStep from the button's map_action_text
            $nextStepUid    = explode('::', $map_action_text);
            $nextStepUid    = $nextStepUid[1];
            // Use that uid to grab the template step that correlates with the origin workflow step from our array
            $nextStep       = $workflowTemplateStepsArray[$nextStepUid]['workflowStep'];
            // Rebuild the map_action_text to use this template step uid instead
            $map_action_text= 'next-step::'.$nextStep->uid;
        }

        $workflowStepItemMap = [
            'page_uid'                      => $workflow->page->uid,
            'workflow_step_uid'             => $workflowStepItem->workflowStep->uid,
            'workflow_step_item_uid'        => $workflowStepItem->uid,
            'workflow_uid'                  => $workflow->uid,
            'map_order'                     => $workflowTemplateStepItemMap->map_order,
            'map_text'                      => $workflowTemplateStepItemMap->map_text,
            'map_action'                    => $workflowTemplateStepItemMap->map_action,
            'map_action_text'               => $map_action_text,
            'workflow_step_item_image_uid'  => null,
        ];

        // If an image was passed we're adding a button to a carousel pane (represented by the StepItemImage) and we want to associate this button to that
        if($workflowStepItemImage !== null)
        {
            $workflowStepItemMap['workflow_step_item_image_uid'] = $workflowStepItemImage->uid;
        }

        $workflowStepItemMap = \App\WorkflowStepItemMap::create($workflowStepItemMap);

        return $workflowStepItemMap;
    }
}
