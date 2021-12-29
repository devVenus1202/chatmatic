<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * App\Workflow
 *
 * @property int $uid
 * @property int $page_uid
 * @property int|null $root_workflow_step_uid
 * @property string $name
 * @property \Carbon\Carbon $created_at_utc
 * @property \Carbon\Carbon $updated_at_utc
 * @property string $workflow_type
 * @property int|null $workflow_template_uid
 * @property string $keywords
 * @property int $messages_delivered
 * @property int $messages_read
 * @property int $messages_clicked
 * @property-read \App\Page $page
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\WorkflowStep[] $workflowSteps
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Workflow whereCreatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Workflow whereKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Workflow whereMessagesClicked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Workflow whereMessagesDelivered($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Workflow whereMessagesRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Workflow whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Workflow wherePageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Workflow whereRootWorkflowStepUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Workflow whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Workflow whereUpdatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Workflow whereWorkflowTemplateUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Workflow whereWorkflowType($value)
 * @mixin \Eloquent
 * @property bool|null $archived
 * @property string|null $archived_at_utc
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Workflow whereArchived($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Workflow whereArchivedAtUtc($value)
 * @property string $keywords_option
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Workflow whereKeywordsOption($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Workflow newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Workflow newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Workflow query()
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Broadcast[] $broadcasts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\QuickReply[] $workflowQuickReplies
 * @property-read int|null $broadcasts_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\WorkflowStepItemMap[] $buttons
 * @property-read int|null $buttons_count
 * @property-read \App\WorkflowStep|null $rootStep
 * @property-read int|null $workflow_quick_replies_count
 * @property-read int|null $workflow_steps_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\WorkflowTrigger[] $workflowTriggers
 * @property-read int|null $workflow_triggers_count
 */
class Workflow extends Model
{
    const CREATED_AT        = 'created_at_utc';
    const UPDATED_AT        = 'updated_at_utc';

    protected $table        = 'workflows';
    protected $primaryKey   = 'uid';

    protected $guarded      = ['uid'];

    /**
     * @return string
     */
    public function getDateFormat()
    {
        return 'Y-m-d H:i:s.u';
    }

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
    public function workflowTriggers()
    {
        return $this->hasMany(WorkflowTrigger::class, 'workflow_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workflowSteps()
    {
        return $this->hasMany(WorkflowStep::class, 'workflow_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rootStep()
    {
        return $this->hasOne(WorkflowStep::class, 'uid', 'root_workflow_step_uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workflowQuickReplies()
    {
        return $this->hasMany(QuickReply::class, 'workflow_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function broadcasts()
    {
        return $this->hasMany(TriggerConfBroadcast::class, 'workflow_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function buttons()
    {
        return $this->hasMany(WorkflowStepItemMap::class, 'workflow_uid', 'uid');
    }
    
    /**
     * Archive this Workflow
     *
     * @return bool
     */
    public function archive()
    {
        $this->archived = true;
        return $this->save();
    }

    /**
     * Un-archive this Workflow
     *
     * @return bool
     */
    public function unArchive()
    {
        $this->archived = false;
        return $this->save();
    }

    /**
     * Delete Workflow-related records on other tables
     *
     * @throws \Exception
     */
    public function deleteWorkflowData()
    {
        // TODO: Need to implement a couple loops here to delete the tags related to Maps (buttons) and QuickReplies

        QuickReply::where('workflow_uid', $this->uid)->delete();
        WorkflowStepItemImage::where('workflow_uid', $this->uid)->delete();
        WorkflowStepItemAudio::where('workflow_uid', $this->uid)->delete();
        WorkflowStepItemVideo::where('workflow_uid', $this->uid)->delete();
        WorkflowStepItemMap::where('workflow_uid', $this->uid)->delete();
        WorkflowStepItem::where('workflow_uid', $this->uid)->delete();
        WorkflowStep::where('workflow_uid', $this->uid)->delete();

        $workflowTriggers = $this->workflowTriggers;
        foreach($workflowTriggers as $trigger){
            switch($trigger->type){
                case "broadcast":
                    $broadcast = $trigger->broadcast;
                    $broadcast->delete();
                    break;
                case "buttons":
                    $button = $trigger->button;
                    $button->delete();
                    break;
                case "keywordmsg":
                    $keyword = $trigger->keyword;
                    $keyword->delete();
                    break;
                case "landing_page":
                    $landPage = $trigger->landingPage;
                    $landPage->delete();
                    break;
                case "m_dot_me":
                    $mdotm = $trigger->mdotme;
                    $mdotm->delte();
                break;
            }
            $trigger->delete();
        }
    }

    /**
     * @return array
     */
    public function generateMessageCountRatios()
    {
        $messages_read_count        = $this->messages_read;
        $messages_delivered_count   = $this->messages_delivered;
        $messages_clicked_count     = $this->messages_clicked;

        // Calculate the ratio of read messages in relation to the number delivered
        $messages_read_ratio        = 0;
        if($messages_read_count > 0 && $messages_delivered_count > 0)
        {
            // More than delivered, or equal to it, we'll hard cap at 100%
            if($messages_read_count >= $messages_delivered_count)
            {
                $messages_read_ratio = 100;
            }
            else
            {
                $messages_read_ratio    = round($messages_read_count / $messages_delivered_count, 2) * 100;
            }
        }

        // Calculate the ratio of clicked messages in relation to the number delivered
        $messages_clicked_ratio     = 0;
        if($messages_clicked_count > 0 && $messages_delivered_count > 0)
        {
            // More than delivered, or equal to it, we'll hard cap at 100%
            if($messages_clicked_count >= $messages_delivered_count)
            {
                $messages_clicked_ratio = 100;
            }
            else
            {
                $messages_clicked_ratio    = round($messages_clicked_count / $messages_delivered_count, 2) * 100;
            }
        }

        return [
            'clicked_ratio' => $messages_clicked_ratio,
            'read_ratio'    => $messages_read_ratio
        ];
    }

    /**
     * @param null $new_workflow_name
     * @return array
     * @throws \Exception
     */
    public function cloneIntoTemplate($new_workflow_name = null)
    {
        $response = [
            'success'   => false,
            'error'     => '',
            'error_msg' => '',
            'template'  => null
        ];

        // Get template-prepared version of this Workflow
        $workflow_copy      = clone $this;
        $workflowTemplate   = \App\Workflow::prepareForTemplate($workflow_copy);

        // If a name was passed to the method we'll use that to overwrite the source workflows name
        if($new_workflow_name !== null)
        {
            if(mb_strlen($new_workflow_name) > 64)
            {
                $response['error']      = 1;
                $response['error_msg']  = 'New template name provided is too long! Maximum is 64 characters.';

                return $response;
            }

            $workflowTemplate['name'] = $new_workflow_name;
        }

        // Previous data needed for the template

        // Let's create the tags mapping
        $tags_mapping = $this->create_tags_mapping();

        \DB::beginTransaction();

        // Create the WorkflowTemplate record
        $workflowTemplate = \App\WorkflowTemplate::create($workflowTemplate);

        // Create the TagsTemplate record and update the tags_mapping
        $tags = array_keys($tags_mapping);
        foreach ($tags as $key => $tag) {

            $tagTemplate = \App\TagTemplate::create([
                'value' => $tag,
                'workflow_template_uid' => $workflowTemplate->uid,
            ]);
            $tags_mapping[$tag]['template_tag_uid'] = $tagTemplate->uid;
        }
        // crete the buttons and quick reply mapping to use on conditionals
        $buttons_mapping    = [];
        $quick_rep_mapping  = [];
        
        // Template created - mirror it's steps
        $workflowStepsArray = [];
        foreach($this->workflowSteps()->orderBy('uid', 'asc')->get() as $key => $workflowStep)
        {
            // Get template-prepared version of this WorkflowStep
            $workflowTemplateStep = \App\WorkflowStep::prepareForTemplate($workflowStep, $workflowTemplate);

            // Create the WorkflowTemplateStep record
            $workflowTemplateStep = \App\WorkflowTemplateStep::create($workflowTemplateStep);

            // If this is the first one, we'll want to set it's UID as the $workflowTemplate->root_workflow_template_step_uid
            if($key === 0)
            {
                $workflowTemplate->root_workflow_template_step_uid = $workflowTemplateStep->uid;
                $workflowTemplate->save();
            }

            // Create an array/map of the origin step uid's correlated to their template representative to use
            // later in re-mapping the button and quick reply actions
            $workflowStepsArray[$workflowStep->uid] = [
                'origin'    => $workflowStep,
                'template'  => $workflowTemplateStep
            ];
        }

        // Now that we've looped through the steps (in completion) we'll go through them again to template out
        // their related items
        foreach($workflowStepsArray as $workflowStepArray)
        {
            $workflowStep           = $workflowStepArray['origin'];
            $workflowTemplateStep   = $workflowStepArray['template'];

            if (isset($workflowStep->child_step_uid))
            {
                $childStep = $workflowStepsArray[$workflowStep->child_step_uid]['template'];
                $workflowTemplateStep['child_step_uid'] = $childStep->uid;
                $workflowTemplateStep->save();
            }

            if ($workflowStep->step_type == 'items')
            {
                // Template Step created - mirror the step items
                foreach($workflowStep->workflowStepItems()->orderBy('uid', 'asc')->get() as $key2 => $workflowStepItem)
                {
                    // Get template-prepared version of this WorkflowStepItem
                    $workflowTemplateStepItem = \App\WorkflowStepItem::prepareForTemplate($workflowStepItem, $workflowTemplate, $workflowTemplateStep);

                    // Create the WorkflowTemplateStepItem
                    $workflowTemplateStepItem = \App\WorkflowTemplateStepItem::create($workflowTemplateStepItem);

                    // Mirror the item map (buttons)
                    // (Getting only the records where 'workflow_step_item_image_uid' == null so we're not grabbing the buttons that are associated to images which is handled below by the carousel handling)
                    foreach($workflowStepItem->workflowStepItemMaps()->whereNull('workflow_step_item_image_uid')->orderBy('uid', 'asc')->get() as $workflowStepItemMap)
                    {
                        // Passing source button, template, template step item and the array map of steps in case this is a 'next-step' button and we need the uid of the next step
                        $workflowTemplateStepItemMap = WorkflowTemplateStepItemMap::createFromWorkflowStepItemMap(
                            $workflowStepItemMap, $workflowTemplate, $workflowTemplateStepItem, $workflowStepsArray);
                        $workflowTemplateStepItemMap->save();

                        // update the buttons mapping
                        $buttons_mapping[$workflowStepItemMap->uid] = $workflowTemplateStepItemMap->uid;

                        // check if this button has assigned a tag
                        $tag_creation = $this->create_taggable_template ($tags_mapping, 'buttons', $workflowTemplateStepItemMap, $workflowStepItemMap);

                        if ($tag_creation['error'] == 1)
                        {
                            $response['error']      = 1;
                            $response['error_msg']  = $tag_creation['error_msg'];

                            return $response;
                        }

                        // If the workflowStepItem is free_text_input type, we have to create a 
                        // custom_field associated to the response
                        if ( $workflowStepItemMap->map_action == 'input' )
                        {
                            $custom_field = $workflowStepItemMap->customField;

                            if ( isset($custom_field) )
                            {
                                // Create a new template custom field
                                $template_custom_field = \App\CustomFieldTemplate::create(
                                    [
                                        'field_name'            => $custom_field->field_name,
                                        'validation_type'       => $custom_field->validation_type,
                                        'template_uid'          => $workflowTemplate->uid,
                                        'merge_tag'             => $custom_field->merge_tag,
                                        'custom_field_type'     => $custom_field->custom_field_type,
                                        'default_value'         => $custom_field->default_value,
                                    ]);

                                // Here we have to update the already  workflow template step item map
                                $workflowTemplateStepItemMap->custom_field_template_uid = $template_custom_field->uid;
                                $workflowTemplateStepItemMap->save();
                            }
                            
                        }

                        // If there was an error thrown we'll return that
                        if(is_array($workflowStepItemMap) && isset($workflowStepItemMap['error']))
                        {
                            return $workflowTemplateStepItemMap;
                        }
                    }

                    // Mirror the step images
                    foreach($workflowStepItem->workflowStepItemImages()->orderBy('uid', 'asc')->get() as $workflowStepItemImage)
                    {
                        /** @var \App\WorkflowStepItemImage $workflowStepItemImage */

                        // Clone media file into a new file and return it's url
                        $new_image_url  = $workflowStepItemImage->cloneMediaForTemplate($workflowTemplate);

                        $workflowTemplateStepItemImage = [
                            'workflow_template_step_item_uid'   => $workflowTemplateStepItem->uid,
                            'workflow_template_uid'             => $workflowTemplate->uid,
                            'image_order'                       => $workflowStepItemImage->image_order,
                            'image_url'                         => $new_image_url,
                            'redirect_url'                      => $workflowStepItemImage->redirect_url,
                            'image_title'                       => $workflowStepItemImage->image_title,
                            'image_subtitle'                    => $workflowStepItemImage->image_subtitle
                        ];
                        $workflowTemplateStepItemImage = \App\WorkflowTemplateStepItemImage::create($workflowTemplateStepItemImage);

                        // Are there buttons associated with this $workflowStepImage? If so it's part of a carousel and we need add them/associate with this image
                        if($workflowStepItemImage->workflowStepItemMaps()->count())
                        {
                            // Loop through the buttons creating them
                            foreach($workflowStepItemImage->workflowStepItemMaps()->orderBy('uid', 'asc')->get() as $workflowStepItemMap)
                            {
                                // Passing source button, template, template step item, template step item image and the array map of steps in case this is a 'next-step' button and we need the uid of the next step
                                $workflowTemplateStepItemMap = WorkflowTemplateStepItemMap::createFromWorkflowStepItemMap(
                                    $workflowStepItemMap, $workflowTemplate, $workflowTemplateStepItem, $workflowStepsArray, $workflowTemplateStepItemImage
                                );

                                $workflowTemplateStepItemMap->save();

                                // update the buttons mapping
                                $buttons_mapping[$workflowStepItemMap->uid] = $workflowTemplateStepItemMap->uid;

                                // Set up assigned tags
                                $tag_creation = $this->create_taggable_template ($tags_mapping, 'buttons', $workflowTemplateStepItemMap, $workflowStepItemMap);

                                if ($tag_creation['error'] == 1)
                                {
                                    $response['error']      = 1;
                                    $response['error_msg']  = $tag_creation['error_msg'];

                                    return $response;
                                }

                                // If there was an error thrown we'll return that
                                if(is_array($workflowStepItemMap) && isset($workflowStepItemMap['error']))
                                {
                                    return $workflowTemplateStepItemMap;
                                }
                            }
                        }
                    }

                    // Mirror step audio
                    foreach($workflowStepItem->workflowStepItemAudios()->orderBy('uid', 'asc')->get() as $workflowStepItemAudio)
                    {
                        /** @var \App\WorkflowStepItemAudio $workflowStepItemAudio */

                        // Clone media file into a new file and return it's url
                        $new_audio_url  = $workflowStepItemAudio->cloneMediaForTemplate($workflowTemplate);

                        $workflowTemplateStepItemAudio = [
                            'workflow_template_step_item_uid'   => $workflowTemplateStepItem->uid,
                            'workflow_template_uid'             => $workflowTemplate->uid,
                            'audio_url'                         => $new_audio_url,
                        ];
                        $workflowTemplateStepItemAudio = \App\WorkflowTemplateStepItemAudio::create($workflowTemplateStepItemAudio);
                    }

                    // Mirror step video
                    foreach($workflowStepItem->workflowStepItemVideos()->orderBy('uid', 'asc')->get() as $workflowStepItemVideo)
                    {
                        /** @var \App\WorkflowStepItemVideo $workflowStepItemVideo */

                        // Clone media file into a new file and return it's url
                        $new_video_url  = $workflowStepItemVideo->cloneMediaForTemplate($workflowTemplate);

                        $workflowTemplateStepItemVideo = [
                            'workflow_template_step_item_uid'   => $workflowTemplateStepItem->uid,
                            'workflow_template_uid'             => $workflowTemplate->uid,
                            'video_url'                         => $new_video_url,
                        ];
                        $workflowTemplateStepItemVideo = \App\WorkflowTemplateStepItemVideo::create($workflowTemplateStepItemVideo);
                    }

                }

                // Mirror quick replies
                foreach($workflowStep->quickReplies()->orderBy('uid', 'asc')->get() as $workflowStepQuickReply)
                {
                    // Get template-prepared version of this WorkflowStepQuickReply
                    $workflowTemplateStepQuickReply = QuickReply::prepareForTemplate($workflowStepQuickReply, $workflowTemplate, $workflowTemplateStep);

                    $map_action_text = $workflowStepQuickReply->map_action_text;
                    // If the map_action_text contains a payload for a "next-step" we'll need to parse the map_action_text
                    // for "next-step::1234", replacing 1234 with the correlating step uid
                    if(mb_stristr($map_action_text, 'next-step'))
                    {
                        // Extract the uid of the next workflowStep from the button's map_action_text
                        $nextStepUid        = explode('::', $map_action_text);
                        $nextStepUid        = $nextStepUid[1];
                        // Use that uid to grab the template step that correlates with the origin workflow step from our array
                        if( ! isset($workflowStepsArray[$nextStepUid]['template']))
                        {
                            $response['error']      = 1;
                            $response['error_msg']  = 'You cannot make a template out of a workflow that triggers other existing workflows. Change that step and try again. (Quick Reply: '.$workflowStepQuickReply->map_text.')';

                            return $response;
                        }
                        $nextStep           = $workflowStepsArray[$nextStepUid]['template'];
                        // Rebuild the map_action_text to use this template step uid instead
                        $map_action_text    = 'next-step::'.$nextStep->uid;
                    }
                    $workflowTemplateStepQuickReply['map_action_text'] = $map_action_text;

                    // Create the WorkflowTemplateStepQuickReply
                    $workflowTemplateStepQuickReply = WorkflowTemplateStepQuickReply::create($workflowTemplateStepQuickReply);
                    $workflowTemplateStepQuickReply->save();

                    // update the buttons mapping
                    $quick_rep_mapping[$workflowStepQuickReply->uid] = $workflowTemplateStepQuickReply->uid;

                    // Set up assigned tags
                    $tag_creation = $this->create_taggable_template ($tags_mapping, 'quick_replies', $workflowTemplateStepQuickReply, $workflowStepQuickReply);

                    if ($tag_creation['error'] == 1)
                    {
                        $response['error']      = 1;
                        $response['error_msg']  = $tag_creation['error_msg'];

                        return $response;
                    }

                    // Clone custom field responses
                    $custom_field = $workflowStepQuickReply->customField;

                    if ( isset($custom_field) )
                    {
                        // find out if we already saved the custom field
                        $already_custom_field = \App\CustomFieldTemplate::where('field_name',$custom_field->field_name)->where('template_uid',$workflowTemplate->uid)->first();

                        if ( ! isset($already_custom_field) )
                        {
                            // Create a new template custom field
                            $template_custom_field = \App\CustomFieldTemplate::create(
                                [
                                        'field_name'            => $custom_field->field_name,
                                        'validation_type'       => $custom_field->validation_type,
                                        'template_uid'          => $workflowTemplate->uid,
                                        'merge_tag'             => $custom_field->merge_tag,
                                        'custom_field_type'     => $custom_field->custom_field_type,
                                        'default_value'         => $custom_field->default_value,
                                ]);

                            // Here we have to update the already  workflow template step item map
                            $workflowTemplateStepQuickReply->custom_field_template_uid = $template_custom_field->uid;
                            $workflowTemplateStepQuickReply->save();
                        }
                    }
                }                

            }
            elseif( $workflowStep->step_type == 'delay' )
            {

                $workflowStepOptionDelay = $workflowStep->optionDelay()->first();

                // Get template-repared version of this WorkflowStepOptionDelay
                $workflowTemplateOptionDelay = \App\WorkflowStepOptionDelay::prepareForTemplate($workflowStepOptionDelay, $workflowTemplateStep);

                // Let's assign the next uid
                $nextStep = $workflowStepsArray[$workflowStepOptionDelay->next_step_uid]['template'];
                $nextStepUid = $nextStep->uid;
                $workflowTemplateOptionDelay['workflow_template_next_step_uid'] = $nextStepUid;

                // Create the WorkflowStepOptionDelay 
                $workflowTemplateOptionDelay = \App\WorkflowTemplateStepOptionDelay::create($workflowTemplateOptionDelay);
            }
            elseif( $workflowStep->step_type == 'randomizer')
            {

                foreach($workflowStep->optionRandomizations()->orderBy('uid','asc')->get() as $key2 => $workflowStepRandom)
                {
                    // Get the template perated version of this workflowStepRandom
                    $workflowTemplateStepRandom = \App\WorkflowStepOptionRandom::prepareForTemplate($workflowStepRandom, $workflowTemplateStep);

                    // Let's assign the next uid
                    $nextStep = $workflowStepsArray[$workflowStepRandom->next_step_uid]['template'];
                    $nextStepUid = $nextStep->uid;
                    $workflowTemplateStepRandom['workflow_template_next_step_uid'] = $nextStepUid;

                    // Create the WorkflowTemplateStepItem
                    $workflowTemplateStepRandom = \App\WorkflowTemplateStepOptionRandom::create($workflowTemplateStepRandom);

                }
            }
            elseif( $workflowStep->step_type == 'conditions')
            {

                foreach($workflowStep->optionConditions()->orderBy('uid','asc')->get() as $key2 => $workflowStepCondition)
                {
                    // Get the template perated version of this workflowStepCond
                    $workflowTemplateStepCondtion = \App\WorkflowStepOptionCondition::prepareForTemplate($workflowStepCondition, 
                                                                                                         $workflowTemplateStep, 
                                                                                                         $tags_mapping,
                                                                                                         $buttons_mapping,
                                                                                                         $quick_rep_mapping);
                    // Let's assign the next uid
                    $nextStep = $workflowStepsArray[$workflowStepCondition->next_step_uid]['template'];
                    $nextStepUid = $nextStep->uid;
                    $workflowTemplateStepCondtion['workflow_template_next_step_uid'] = $nextStepUid;

                    // Create the WorkflowTemplateStepItem
                    $workflowTemplateStepCondtion = \App\WorkflowTemplateStepOptionCondition::create($workflowTemplateStepCondtion);

                }
            }
            elseif( $workflowStep->step_type == 'sms')
            {

                $workflowStepSms = $workflowStep->optionSms()->first();

                // Get template-repared version of this WorkflowSms
                $workflowTemplateSms = \App\WorkflowStepSms::prepareForTemplate($workflowStepSms, $workflowTemplateStep);

                // Create the WorkflowStepSms
                $workflowTemplateSms = \App\WorkflowTemplateStepSms::create($workflowTemplateSms);

            }
            
        }

        \DB::commit();

        $response['success']    = true;
        $response['template']   = $workflowTemplate;

        return $response;
    }

    /**
     * @return array
     */
    public function generateStatsForUI()
    {

        /*
         * Stats to collect...
         *
         * Workflow:
         * - Total Messages Sent
         * - Total # of Subscribers
         * - Percentage of subscribers that have completed the workflow (all steps)
         * - Avg # of messages per user
         * - # of emails collected
         * - % opt-in rate
         *
         * Per-Step:
         * - Message Number
         * - Total Subscriber Count
         * - "Reached" (# of subscribers that made it to this step)
         * - % of Reached / Total Subscribers
         *
         */

        $return = [

        ];

        $return['total_messages_sent']                          = 0;
        $return['total_subscribers']                            = 0;
        $return['percent_subscribers_completed_all_steps']      = 0;
        $return['average_number_of_messages_per_subscriber']    = 0;
        $return['workflow_has_email_collection']                = false;
        $return['total_emails_collected']                       = 0;
        $return['percent_opt_in']                               = 0;

        // Get a count of all subscribers for this workflow
        $result = DB::select(DB::raw('
            SELECT COUNT(*)
            FROM (
                SELECT DISTINCT ON ("subscriber_psid") subscriber_psid 
                FROM "interactions" 
                WHERE "workflow_uid" = \''.$this->uid.'\'
            )
            AS temp;
        '));
        $total_subscriber_count = $result[0]->count;
        // Set value for return array
        $return['total_subscribers'] = $total_subscriber_count;

        // Get a count of all messages sent by this workflow
        $total_messages_count = Interaction::where('workflow_uid', $this->uid)->count();
        // Set value for return array
        $return['total_messages_sent'] = $total_messages_count;

        // The return value that we need to send back for 'average_number_of_messages_per_subscriber is a simple
        // division of total_messages / subscriber_count
        $average_number_of_messages_per_subscriber = 0;
        if($total_messages_count > 0 && $total_subscriber_count > 0)
            $average_number_of_messages_per_subscriber = round($total_messages_count / $total_subscriber_count, 1);
        // Set value for return array
        $return['average_number_of_messages_per_subscriber'] = $average_number_of_messages_per_subscriber;

        // To calculate the 'total_emails_collected' & 'percent_opt_in' we're going to need to first determine if there are actually any
        // email quick replies on this workflow, if not that's an easy zero. So we'll set a flag here that we'll later
        // modify if an email quick rep is found.
        $workflow_has_email_collection = false;
        $email_quick_reply = $this->workflowQuickReplies()->where('type', 'email')->first();
        if($email_quick_reply)
        {
            $workflow_has_email_collection = true;
            $return['workflow_has_email_collection'] = $workflow_has_email_collection;
        }

        // Get collection of all steps on this workflow
        $steps      = $this->workflowSteps()->orderBy('uid', 'ASC')->get();

        $step_counts = [];
        $last_step_reached_count = 0;
        // Loop through each step to determine how many subscribers have reached it
        foreach($steps as $step)
        {
            $step_uid = (int) $step->uid;

            // Get a count of the number of subscribers that have reached this step
            $result = DB::select(
                DB::raw('
                SELECT COUNT(*) 
                FROM (
                    SELECT DISTINCT ON ("subscriber_psid") subscriber_psid 
                    FROM "interactions" 
                    WHERE "workflow_uid" = \''.$this->uid.'\' 
                    AND "workflow_step_uid" = \''.$step_uid.'\'
                ) 
                AS temp;
                ')
            );
            $subscribers_hit_this_step_count = $result[0]->count;

            $step_results = [
                'name'              => '',
                'reached'           => null,
                'percent_reached'   => null,
            ];

            $step_results['name']       = $step->name;
            $step_results['reached']    = $subscribers_hit_this_step_count;
            if($total_subscriber_count > 0)
                $step_results['percent_reached']    = round(($subscribers_hit_this_step_count / $total_subscriber_count), 2) * 100;
            else
                $step_results['percent_reached']    = 0;

            $step_counts[$step_uid] = (object) $step_results;

            $step->subscribers_have_reached_this_step = $subscribers_hit_this_step_count;

            // Set the number of subscribers that reached this step as the last step, so it'll be overwritten until the last step, then we have it for
            // use in calculating the percent who made it to the final step
            $last_step_reached_count = $subscribers_hit_this_step_count;
        }

        // Calculate the percent of subscribers that reached all steps
        $percent_reached_all_steps = 0;
        if($last_step_reached_count > 0)
        {
            $percent_reached_all_steps = round($last_step_reached_count / $total_subscriber_count, 2) * 100;
        }
        $return['percent_subscribers_completed_all_steps'] = $percent_reached_all_steps;

        // Now that we've collected the basic step information we're going to loop through again and capture the email collection optin rates

        // Does this workflow collect email information?
        // If this flag is false we'll want to check this step to determine if there's an email collection method
        if($workflow_has_email_collection)
        {
            $total_emails_collected = 0;
            foreach($steps as $step)
            {
                // Probably need to run a query to determine if there's an email quick rep
                $quick_reply = $step->quickReplies()->where('type', 'email')->first();

                // If it does have one... need to calculate how many optin's it's had and calculate a percentage
                if($quick_reply)
                {
                    // What is the next step?
                    $email_qr_next_step_uid = (int) str_ireplace('next-step::', '', $quick_reply->map_action_text);

                    // Get a count of the number of subscribers that have reached this step
                    $email_qr_submitted_count = $step_counts[$email_qr_next_step_uid]->reached;

                    $total_emails_collected += $email_qr_submitted_count;
                }
            }

            $return['total_emails_collected'] = $total_emails_collected;
            $return['percent_opt_in']           = round($total_emails_collected / $total_subscriber_count, 2) * 100;
        }

        $return['steps_data'] = $step_counts;

        return $return;
    }

    /**
     * @return array
     */
    public function generateStatisticsForUI()
    {

        $response = [

        ];

        $messages_sent = 0;
        $messages_delivered = 0;
        $messages_clicked = 0;
        $messages_opened = 0;

        $steps = $this->workflowSteps()->get();

        foreach($steps as $step)
        {
            $messages_sent += $step->messages_delivered;
            $messages_delivered += $step->messages_reached;
            $messages_clicked += $step->messages_clicked;
            $messages_opened += $step->messages_read;
        }


        $response['workflow']['sent']           = $messages_sent;
        $response['workflow']['delivered']      = $messages_delivered;
        $response['workflow']['clicked']        = $messages_clicked;
        $response['workflow']['opened']         = $messages_opened;

        // Steps

        $steps = $this->workflowSteps()->get();
        $response['steps'] = [];

        foreach($steps as $step){
            $step_data = [];

            $step_data['uid'] = $step->uid;

            $step_data['sent'] = $step->messages_delivered;
            $step_data['delivered'] = $step->messages_reached;
            $step_data['clicked'] = $step->messages_clicked;
            $step_data['opened'] = $step->messages_read;

            // click counters
            $step_data['buttons'] = [];
            $items = $step->workflowStepItems()->get();
            $step_buttons = [];
            foreach ($items as $item) 
            {
                $buttons = $item->workflowStepItemMaps()->get();
                foreach ($buttons as $button) 
                {
                    $step_buttons['uid']        = $button->uid;
                    $step_buttons['clicks']     = $button->clicks;

                    $step_data['buttons'][] = $step_buttons;
                }
            }

            // for quick replies
            $step_data['quick_replies'] = [];
            $quick_replies = $step->quickReplies()->get();
            $step_quick_reps = [];
            foreach ($quick_replies as $quick_reply)
            {
                $step_quick_reps['uid']         = $quick_reply->uid;
                $step_quick_reps['clicks']      = $quick_reply->clicks;

                $step_data['quick_replies'][] = $step_quick_reps;
            }
            

            $response['steps'][] = $step_data;

        }


        return $response;

    }

    /**
     * Prepare this Workflow record object to be templated by removing unneeded values and setting
     * appropriate template values
     *
     * @param Workflow $workflow
     * @return array
     */
    public static function prepareForTemplate(Workflow $workflow)
    {
        // Create a template from the workflow..
        $origin_workflow = clone $workflow;
        $page = $workflow->page;

        // Unset the stuff we don't need/will replace
        unset($workflow->uid,
            $workflow->page_uid,
            $workflow->picture_url,
            $workflow->root_workflow_step_uid,
            $workflow->created_at_utc,
            $workflow->updated_at_utc,
            $workflow->workflow_template_uid,
            $workflow->messages_delivered,
            $workflow->messages_read,
            $workflow->messages_clicked,
            $workflow->archived,
            $workflow->archived_at_utc
        );

        // Cast the object to an array
        $workflowTemplate = $workflow->toArray();

        // Loop through and dump any of the relationship arrays
        foreach($workflowTemplate as $key => $value)
        {
            if(is_array($value))
                unset($workflowTemplate[$key]);
        }

        // Set the stuff we need
        $workflowTemplate['chatmatic_user_uid']                 = (int) $page->created_by; // TODO: This should be pulled from the logged in user?
        $workflowTemplate['origin_workflow_uid']                = (int) $origin_workflow->uid;
        $workflowTemplate['root_workflow_template_step_uid']    = null;

        // Return the array
        return $workflowTemplate;
    }

    /**
     * Build array with messages constructed ready for POST'ing to FB SDK
     *
     *  Array
    (
    [0] => Array
    (
    [messaging_type] => RESPONSE
    [recipient] => Array
    (
    [id] => 1682489885197875
    )
    [message] => Array
    (
    [text] => Message 1
    )
    )
    [1] => Array
    (
    [messaging_type] => RESPONSE
    [recipient] => Array
    (
    [id] => 1682489885197875
    )
    [message] => Array
    (
    [text] => Message 2
    )
    )
    )
     *
     * @param $subscriber_psid
     * @return array
     * @throws \Exception
     */
    public function buildMessagePostArray($subscriber_psid)
    {
        $messages = [];
        // Grab workflow step from the workflow
        $workflow_steps = $this->workflowSteps()->orderBy('uid', 'ASC')->get();
        if($workflow_steps)
        {
            // Loop through the steps to determine what messages should be ignored as they're responses to payloads
            $ignore_steps = [];
            foreach($workflow_steps as $workflow_step)
            {
                $workflow_step_items = $workflow_step->workflowStepItems()->orderBy('uid', 'ASC')->get();
                foreach($workflow_step_items as $workflow_step_item)
                {
                    $workflow_step_item_maps = $workflow_step_item->workflowStepItemMaps()->orderBy('uid', 'asc')->get();
                    if($workflow_step_item_maps)
                    {
                        foreach($workflow_step_item_maps as $workflow_step_item_map)
                        {
                            // If there's a button action we'll parse the workflow_step uid from it
                            if($workflow_step_item_map->map_action == 'postback'
                                && mb_strlen($workflow_step_item_map->map_action_text)
                                && mb_stristr($workflow_step_item_map->map_action_text, '::'))
                            {
                                // We've found a button with a postback to another message, so we don't want to send that message now
                                $ignore_workflow_step_uid = explode('::', $workflow_step_item_map->map_action_text);
                                $ignore_workflow_step_uid = $ignore_workflow_step_uid[1];
                                $ignore_steps[] = $ignore_workflow_step_uid;
                            }
                        }
                    }
                }
            }

            foreach($workflow_steps as $workflow_step)
            {
                // If this $workflow_step is in the $ignore_steps we'll skip sending it for now since it's meant to be a response
                // to a postback
                if(in_array($workflow_step->uid, $ignore_steps))
                    break;

                // Grab the workflow step item and turn it into a message we'll send
                $workflow_step_items = $workflow_step->workflowStepItems()->orderBy('uid', 'ASC')->get();
                if( ! $workflow_step_items)
                {
                    throw new \Exception('Workflow Step Item not found attempting to send '.$this->workflow_type.'. PageUID: '
                        .$this->page->uid.' WorkflowUID:'. $this->uid.' WorkflowStepUID: '.$workflow_step->uid);
                }

                // Loop through the steps creating the message(s)
                foreach($workflow_step_items as $workflow_step_item)
                {
                    // Message bucket
                    $message_parameters = [];
                    $message_parameters['recipient']['id'] = $subscriber_psid;

                    switch($workflow_step_item->item_type)
                    {
                        case "text":
                            // Set the text
                            $message_parameters['messaging_type'] = 'RESPONSE';
                            // Process merge fields
                            $workflow_step_item->processMergeFields($subscriber_psid);
                            $message_parameters['message']['text'] = $workflow_step_item->text_message;

                            // Check for buttons and such
                            $buttons = $workflow_step_item->buildButtonsArray();
                            if(count($buttons))
                                $message_parameters['message']['buttons'] = $buttons;
                            break;

                        case "card":
                            $message_parameters['message']['attachment']['type'] = 'template';
                            $message_parameters['message']['attachment']['payload']['template_type'] = 'generic';

                            // Get and set the card's headline/description
                            $card_elements = [];
                            $card_elements['title'] = $workflow_step_item->headline;
                            $card_elements['subtitle'] = $workflow_step_item->content;

                            // Get and set the card's image information
                            $image = $workflow_step_item->workflowStepItemImages()->first();
                            $card_elements['image_url'] = $image->image_url;

                            // If there's a redirect url we'll set that up here
                            if($image->redirect_url !== '')
                                $card_elements['default_action'] = [
                                    'type'  => 'web_url',
                                    'url'   => $image->redirect_url,
                                    'webview_height_ratio' => 'tall'
                                ];

                            // Check for buttons and such
                            $buttons = $workflow_step_item->buildButtonsArray();
                            if(count($buttons))
                                $card_elements['buttons'] = $buttons;

                            // Attach the $card_elements to the request array as the payload elements
                            $message_parameters['message']['attachment']['payload']['elements'] = [
                                $card_elements
                            ];

                            break;

                        case "carousel":
                            $message_parameters['message']['attachment']['type'] = 'template';
                            $message_parameters['message']['attachment']['payload']['template_type'] = 'generic';

                            $card_elements = [];
                            // Get the images
                            $images = $workflow_step_item->workflowStepItemImages()->get();
                            foreach($images as $image)
                            {
                                $element['title']       = $image->image_title;
                                $element['subtitle']    = $image->image_subtitle;
                                $element['image_url']   = $image->image_url;

                                // If there's a redirect url we'll set that up here
                                if($image->redirect_url !== '')
                                    $element['default_action'] = [
                                        'type'  => 'web_url',
                                        'url'   => $image->redirect_url,
                                        'webview_height_ratio' => 'tall'
                                    ];

                                // Check for buttons and such
                                $buttons = $workflow_step_item->buildButtonsArray();
                                if(count($buttons))
                                    $element['buttons'] = $buttons;

                                $card_elements[] = $element;
                            }

                            // Attach the $card_elements to the request array as the payload elements
                            $message_parameters['message']['attachment']['payload']['elements'] = $card_elements;
                            break;

                        case "image":
                            // Get the image
                            $image = $workflow_step_item->workflowStepItemImages()->first();

                            $message_parameters['message']['attachment']['type'] = 'image';
                            $message_parameters['message']['attachment']['payload'] = [
                                'url' => $image->image_url,
                                'is_reusable' => true
                            ];

                            break;

                        case "delay":
                            // The delay's parameters are stored as a json payload in the 'content' field..
                            // The json object is using single quotes, which PHP won't parse.. we'll replace them here
                            $delay_parameters = str_replace("'", '"', $workflow_step_item->content);
                            $delay_parameters = json_decode($delay_parameters, true);
                            // If the typing indicator is set to true we'll show it
                            $message_parameters['typing_indicator'] = false;
                            if($delay_parameters['typing'] == 'true')
                            {
                                $message_parameters['typing_indicator'] = true;
                            }

                            // Set the # of seconds the delay will proceed (to later be removed when sending)
                            $message_parameters['delay'] = $delay_parameters['delay'];

                            break;
                    }

                    // Drop this message into the message bucket
                    $messages[] = $message_parameters;
                }
            }
        }
        else {
            throw new \Exception('Workflow Step not found attempting to send '.$this->workflow_type.'. PageUID: '
                .$this->page->uid.' WorkflowUID:'. $this->uid);
        }

        return $messages;
    }

    private function create_tags_mapping()
    {
        $tags_mapping = [];
        $buttons_tags = $this->buttons;
        $qr_tags = $this->workflowQuickReplies;

        foreach ($buttons_tags as $button) {
            $taggables = \App\Taggable::where('taggable_uid',$button->uid)->where('taggable_type','App\WorkflowStepItemMap')->get();
            foreach ($taggables as $taggable) 
            {
                if (isset($taggable))
                {
                
                    $tags_mapping[$taggable->tag->value]['buttons'][] = $button->uid;
                    $tags_mapping[$taggable->tag->value]['tag_uid'] = $taggable->tag_uid;
                }    
            }
        }

        foreach ($qr_tags as $quick_reply) {
            $taggables = \App\Taggable::where('taggable_uid',$quick_reply->uid)->where('taggable_type','App\QuickReply')->get();
            foreach ($taggables as $taggable)
            {
                if (isset($taggable))
                {
                    $tags_mapping[$taggable->tag->value]['quick_replies'][] = $quick_reply->uid;
                    $tags_mapping[$taggable->tag->value]['tag_uid'] = $taggable->tag_uid;
                }
            }
        }

        return $tags_mapping;
    }

    private function create_taggable_template ($tags_mapping, $postback_element, $new_element, $actual_element)
    {
        // Postback elements
        // buttons, quick_replies

        $response = [
            'error'     => 0,
            'success'   => 0,
            'error_msg' => ''
        ];

        if ($postback_element == 'buttons')
        {
            $taggable_type = 'App\WorkflowStepItemMap';
        }
        elseif ($postback_element == 'quick_replies')
        {
            $taggable_type = 'App\QuickReply';
        }

        try 
        {
            // check if this button has assigned a tag
            foreach ($tags_mapping as $key => $tag_map) 
            {
                $map_buttons = array_keys($tag_map);
                if( in_array($postback_element, $map_buttons))
                {
                    if( in_array($actual_element->uid, $tag_map[$postback_element] ))
                    {
                        // This postbacK_element has assigned a tag
                        // let's use the template_tag_uid to create a new
                        // record on taggables table

                        \App\TaggableTemplate::create([
                            'tag_template_uid' => $tag_map['template_tag_uid'],
                            'taggable_template_uid' => $new_element->uid,
                            'taggable_type' => $taggable_type
                        ]);
                    }
                }
            }
            $response['success'] = 1;

        } catch (\Exception $e) {

            $response['error'] = 1;
            $response['error_msg'] = 'Error creating tags for this template '.$e->getMessage();
        }

        return $response;
        
    }

    public static function validateRestrictions($first_step)
    {
        // Valiadate the use viability for this workflow
        $to_json            = true;
        $to_private_reply   = true;

        if ($first_step['type'] == 'items' )
        {
            $items = $first_step['items'];

            // The first step can't has free text input or delay items
            foreach ($items as $item)
            {
                if ($item['type'] == "free_text_input")
                {
                    $to_json            = false;
                    $to_private_reply   = false;
                }

                if ($item['type'] == "delay")
                {
                    $to_json            = false;
                }
            }

            // The first step of a Private reply can't has more than a single item
            if ( count($items) > 1 )
            {
                $to_private_reply = false;
            }
            $quick_replies = $first_step['quick_replies'];
            if(count($quick_replies))
            {
                foreach ($quick_replies as $quick_reply) {
                    // Check the quick reply avaliability for private replies
                    if ( in_array($quick_reply['reply_type'], array('email','phone')))
                    {
                        $to_private_reply   = false;
                        $to_json            = false;
                    }
                }
            }
        }

        $response = [
            'to_json'           => $to_json,
            'to_private_rep'    => $to_private_reply
        ];

        return $response;

    }
}
