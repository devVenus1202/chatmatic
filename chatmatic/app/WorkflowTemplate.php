<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

/**
 * App\WorkflowTemplate
 *
 * @property int $uid
 * @property int|null $chatmatic_user_uid
 * @property int|null $root_workflow_template_step_uid
 * @property string $name
 * @property \Carbon\Carbon $created_at_utc
 * @property \Carbon\Carbon $updated_at_utc
 * @property string $workflow_type
 * @property string $keywords
 * @property bool $archived
 * @property string|null $archived_at_utc
 * @property int|null $origin_workflow_uid
 * @property-read \App\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\WorkflowTemplateStep[] $workflowTemplateSteps
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplate whereArchived($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplate whereArchivedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplate whereChatmaticUserUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplate whereCreatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplate whereKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplate whereOriginWorkflowUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplate whereRootWorkflowTemplateStepUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplate whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplate whereUpdatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplate whereWorkflowType($value)
 * @mixin \Eloquent
 * @property string $keywords_option
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplate whereKeywordsOption($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplate query()
 * @property-read \App\Workflow|null $workflow
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\WorkflowTemplateStepQuickReply[] $workflowTemplateQuickReplies
 * @property-read int|null $workflow_template_quick_replies_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\WorkflowTemplateStepItemMap[] $workflowTemplateStepItemMaps
 * @property-read int|null $workflow_template_step_item_maps_count
 * @property-read int|null $workflow_template_steps_count
 */
class WorkflowTemplate extends Model
{
    use Searchable;

    const CREATED_AT        = 'created_at_utc';
    const UPDATED_AT        = 'updated_at_utc';

    protected $table        = 'workflow_templates';
    protected $primaryKey   = 'uid';
    protected $guarded      = ['uid'];

    /**
     * @return string
     */
    public function getDateFormat()
    {
        return 'Y-m-d H:i:s.u';
    }

    public function toSearchableArray()
    {
        $array = $this->toArray();

        unset(
            $array['chatmatic_user_uid'],
            $array['root_workflow_template_step_uid'],
            $array['created_at_utc'],
            $array['updated_at_utc'],
            $array['archived'],
            $array['archived_at_utc'],
            $array['origin_workflow_uid'],
            $array['description'],
            $array['price'],
            $array['video_url'],
            $array['category'],
            $array['public'],
            $array['published'],
            $array['picture_url']
        );

        return $array;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'chatmatic_user_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflow()
    {
        return $this->belongsTo(Workflow::class, 'origin_workflow_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workflowTemplateSteps()
    {
        return $this->hasMany(WorkflowTemplateStep::class, 'workflow_template_uid', 'uid');
    }

     /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workflowTemplateQuickReplies()
    {
        return $this->hasMany(WorkflowTemplateStepQuickReply::class, 'workflow_template_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workflowTemplateStepItemMaps()
    {
        return $this->hasMany(WorkflowTemplateStepItemMap::class, 'workflow_template_uid', 'uid');
    }

    /**
     * Archive this WorkflowTemplate
     *
     * @return bool
     */
    public function archive()
    {
        $this->archived = true;
        return $this->save();
    }

    /**
     * Un-archive this WorkflowTemplate
     *
     * @return bool
     */
    public function unArchive()
    {
        $this->archived = false;
        return $this->save();
    }

    /**
     * Push this WorkflowTemplate to a Page as a new Workflow
     *
     * @param Page $page
     * @param $new_workflow_name
     * @return array
     * @throws \Exception
     */
    public function pushToPage(Page $page, $new_workflow_name = null)
    {
        $response = [
            'success'   => 0,
            'error'     => 0,
            'error_msg' => '',
            'workflow'  => []
        ];

        \DB::beginTransaction();

        // Get workflow-prepared version of this WorkflowTemplate
        $template_copy  = clone $this;
        $workflow       = \App\WorkflowTemplate::prepareForWorkflow($template_copy, $page);

        if($new_workflow_name !== null)
        {
            // TODO: Validation on this name
            $workflow['name'] = $new_workflow_name;
        }

        // Check for duplicate by name
        $duplicate = $page->workflows()->where('name', $workflow['name'])->first();
        if($duplicate)
        {
            $workflow['name'] = $workflow['name'].rand(100,999);
        }

        $workflow['workflow_template_uid'] = $this->uid;

        // Let's find out if the template has a tag
        $tags_mapping = $this->create_tags_mapping();

        // Create the Workflow record
        $workflow = \App\Workflow::create($workflow);

        // Create the Tags records
        $tags = array_keys($tags_mapping);
        foreach ($tags as $key => $tag) {

            // We have to find out if this page already has an identical tag
            $pageTag = $page->tags()->where('value',$tag)->first();

            if( ! isset($pageTag))
            {
                $newTag = \App\Tag::create([
                    'value'    => $tag,
                    'keyword'  => '',
                    'page_uid' => $page->uid
                ]);
                $tags_mapping[$tag]['tag_uid'] = $newTag->uid;
            }
            else
            {
                $tags_mapping[$tag]['tag_uid'] = $pageTag->uid;
            }
        }
        // crete the buttons and quick reply mapping to use on conditionals
        $buttons_mapping    = [];
        $quick_rep_mapping  = [];

        // Workflow created - mirror it's steps
        $workflowTemplateStepsArray = [];
        foreach($this->workflowTemplateSteps()->orderBy('uid', 'ASC')->get() as $key => $workflowTemplateStep)
        {
            // Get workflow-prepared version of this WorkflowTemplateStep
            $workflowStep = \App\WorkflowTemplateStep::prepareForWorkflowStep($workflowTemplateStep, $workflow);

            // Create the WorkflowStep record
            $workflowStep = \App\WorkflowStep::create($workflowStep);

            // If this is the first one, we'll want to set it's UID as the $workflow->root_workflow_step_uid
            if($key === 0)
            {
                $workflow->root_workflow_step_uid = $workflowStep->uid;
                $workflow->save();
            }

            // Create an array/map of the origin template step uid's correlated to their workflowStep representative to use
            // later in re-mapping the button actions
            $workflowTemplateStepsArray[$workflowTemplateStep->uid] = [
                'origin'        => $workflowTemplateStep,
                'workflowStep'  => $workflowStep
            ];
        }

        // Now that we've looped through the steps (in completion) we'll go through them again to template out
        // their items
        foreach($workflowTemplateStepsArray as $workflowTemplateStepArray)
        {
            $workflowTemplateStep   = $workflowTemplateStepArray['origin'];
            $workflowStep           = $workflowTemplateStepArray['workflowStep'];

            if (isset($workflowTemplateStep->child_step_uid))
            {
                $childStep = $workflowTemplateStepsArray[$workflowTemplateStep->child_step_uid]['workflowStep'];
                $workflowStep['child_step_uid'] = $childStep->uid;
                $workflowStep->save();
            }

            if ($workflowTemplateStep->step_type == 'items')
            {
                // Workflow Step created - mirror the template step items
                foreach($workflowTemplateStep->workflowTemplateStepItems()->orderBy('uid', 'asc')->get() as $key2 => $workflowTemplateStepItem)
                {
                    // Get workflowStep-prepared version of this WorkflowTemplateStepItem
                    $workflowStepItem = \App\WorkflowTemplateStepItem::prepareForStepItem($workflowTemplateStepItem, $workflow, $workflowStep);

                    // Create the WorkflowTemplateStepItem
                    $workflowStepItem = \App\WorkflowStepItem::create($workflowStepItem);

                    // Workflow Step Item created - mirror the item map (buttons)
                    foreach($workflowTemplateStepItem->workflowTemplateStepItemMaps()->whereNull('workflow_template_step_item_image_uid')->orderBy('uid', 'asc')->get() as $workflowTemplateStepItemMap)
                    {
                        // Passing source button, template, template step item, template step item image and the array map of steps in case this is a 'next-step' button and we need the uid of the next step
                        $workflowStepItemMap = WorkflowStepItemMap::createFromWorkflowTemplateStepItemMap(
                            $workflowTemplateStepItemMap, $workflow, $workflowStepItem, $workflowTemplateStepsArray
                        );
                        $workflowStepItemMap->save();

                        // update the buttons mapping
                        $buttons_mapping[$workflowTemplateStepItemMap->uid] = $workflowStepItemMap->uid;

                        // check if this button has assigned a tag
                        $tag_creation = $this->create_taggable_template ($tags_mapping, 'buttons', $workflowStepItemMap, $workflowTemplateStepItemMap);

                        if ($tag_creation['error'] == 1)
                        {
                            $response['error']      = 1;
                            $response['error_msg']  = $tag_creation['error_msg'];

                            return $response;
                        }

                        // Find out if this step item map is input type
                        if ($workflowTemplateStepItemMap->map_action == 'input')
                        {
                            $custom_field_template = $workflowTemplateStepItemMap->customField;

                            // old templates didn't have related custom fields, we can't do anything for this
                            if (isset($custom_field_template))
                            {
                                // Now we have to check if the page already has a custom field like this
                                // If the page doesn't have let's create this otherwise we to create a new 
                                // one pretty simmilar adding up 1

                                $custom_field_data = [
                                        'validation_type'       => $custom_field_template->validation_type,
                                        'page_uid'              => $page->uid,
                                        'merge_tag'             => $custom_field_template->merge_tag,
                                        'custom_field_type'     => $custom_field_template->custom_field_type,
                                        'default_value'         => $custom_field_template->default_value,
                                        'archived'              => false
                                    ];

                                $already_custom_field = $page->customFields()->where('field_name', $custom_field_template->field_name)->first();

                                if(! isset($already_custom_field))
                                {
                                    // If the page already hasn't an equal custom field we create a new one.
                                    $custom_field_data['field_name']    = $custom_field_template->field_name;
                                    $custom_field_data['merge_tag']     = $custom_field_template->merge_tag;

                                    $custom_field = \App\CustomField::create($custom_field_data);
                                }
                                else
                                {
                                    // Skip and use the already one
                                    $custom_field = $already_custom_field;
                                }
                                

                                // Now it's time to update the step item map
                                $workflowStepItemMap->custom_field_uid = $custom_field->uid;
                                $workflowStepItemMap->save();    
                            }
                        }

                    }

                    // Workflow Step Item Map created - mirror the step images
                    foreach($workflowTemplateStepItem->workflowTemplateStepItemImages()->orderBy('uid', 'asc')->get() as $workflowTemplateStepItemImage)
                    {
                        // Clone media file into a new file and return it's url
                        $new_image_url  = $workflowTemplateStepItemImage->cloneMediaForWorkflow($workflow);

                        $workflowStepItemImage = [
                            'workflow_step_item_uid'    => $workflowStepItem->uid,
                            'image_order'               => $workflowTemplateStepItemImage->image_order,
                            'image_url'                 => $new_image_url,
                            'redirect_url'              => $workflowTemplateStepItemImage->redirect_url,
                            'image_title'               => $workflowTemplateStepItemImage->image_title,
                            'image_subtitle'            => $workflowTemplateStepItemImage->image_subtitle,
                            'page_uid'                  => $workflow->page_uid,
                            'workflow_uid'              => $workflow->uid,
                            'workflow_step_uid'         => $workflowStep->uid
                        ];

                        $workflowStepItemImage = \App\WorkflowStepItemImage::create($workflowStepItemImage);

                        // If there are buttons on this step than we'll need an attachment_id to send the images - so we'll get that now
                        if($workflowStepItem->workflowStepItemMaps()->count() > 0)
                        {
                            $workflowStepItemImage->getFacebookAttachmentId();
                        }

                        // Are there buttons associated with this $workflowStepImage? If so it's part of a carousel and we need add them/associate with this image
                        if($workflowTemplateStepItemImage->workflowTemplateStepItemMaps()->count())
                        {
                            // Loop through the buttons creating them
                            foreach($workflowTemplateStepItemImage->workflowTemplateStepItemMaps()->orderBy('uid', 'asc')->get() as $workflowTemplateStepItemMap)
                            {
                                // Passing source button, template, template step item, template step item image and the array map of steps in case this is a 'next-step' button and we need the uid of the next step
                                $workflowStepItemMap = WorkflowStepItemMap::createFromWorkflowTemplateStepItemMap(
                                    $workflowTemplateStepItemMap, $workflow, $workflowStepItem, $workflowTemplateStepsArray, $workflowStepItemImage
                                );
                                $workflowStepItemMap->save();

                                // update the buttons mapping
                                $buttons_mapping[$workflowTemplateStepItemMap->uid] = $workflowStepItemMap->uid;

                                // check if this button has assigned a tag
                                $tag_creation = $this->create_taggable_template ($tags_mapping, 'buttons', $workflowStepItemMap, $workflowTemplateStepItemMap);

                                if ($tag_creation['error'] == 1)
                                {
                                    $response['error']      = 1;
                                    $response['error_msg']  = $tag_creation['error_msg'];

                                    return $response;
                                }
                            }
                        }
                    }

                    // Mirror step audio
                    foreach($workflowTemplateStepItem->workflowTemplateStepItemAudios()->orderBy('uid', 'asc')->get() as $workflowTemplateStepItemAudio)
                    {
                        /** @var \App\WorkflowTemplateStepItemAudio $workflowTemplateStepItemAudio */

                        // Clone media file into a new file and return it's url
                        $new_audio_url  = $workflowTemplateStepItemAudio->cloneMediaForWorkflow($workflow);

                        $workflowStepItemAudio = [
                            'workflow_step_item_uid'    => $workflowStepItem->uid,
                            'workflow_step_uid'         => $workflowStepItem->workflowStep->uid,
                            'workflow_uid'              => $workflow->uid,
                            'page_uid'                  => $workflow->page->uid,
                            'audio_url'                 => $new_audio_url,
                        ];
                        $workflowStepItemAudio = \App\WorkflowStepItemAudio::create($workflowStepItemAudio);
                    }

                    // Mirror step video
                    foreach($workflowTemplateStepItem->workflowTemplateStepItemVideos()->orderBy('uid', 'asc')->get() as $workflowTemplateStepItemVideo)
                    {
                        /** @var \App\WorkflowTemplateStepItemVideo $workflowTemplateStepItemVideo */

                        // Clone media file into a new file and return it's url
                        $new_video_url  = $workflowTemplateStepItemVideo->cloneMediaForWorkflow($workflow);

                        $workflowStepItemVideo = [
                            'workflow_step_item_uid'    => $workflowStepItem->uid,
                            'workflow_step_uid'         => $workflowStepItem->workflowStep->uid,
                            'workflow_uid'              => $workflow->uid,
                            'page_uid'                  => $workflow->page->uid,
                            'video_url'                 => $new_video_url,
                        ];
                        $workflowStepItemVideo = \App\WorkflowStepItemVideo::create($workflowStepItemVideo);

                        // If there are buttons on this step than we'll need an attachment_id to send the video - so we'll get that now
                        if($workflowStepItem->workflowStepItemMaps()->count() > 0)
                        {
                            $workflowStepItemVideo->getFacebookAttachmentId();
                        }
                    }
                }
                // Mirror quick replies
                foreach($workflowTemplateStep->workflowTemplateStepQuickReplies()->orderBy('uid', 'asc')->get() as $workflowTemplateStepQuickReply)
                {
                    // Get workflow-prepared version of this WorkflowStepQuickReply
                    $workflowStepQuickReply = WorkflowTemplateStepQuickReply::prepareForWorkflow($workflowTemplateStepQuickReply, $workflow, $workflowStep);

                    $map_action_text = $workflowTemplateStepQuickReply->map_action_text;
                    // If the map_action_text contains a payload for a "next-step" we'll need to parse the map_action_text
                    // for "next-step::1234", replacing 1234 with the correlating step uid
                    if(mb_stristr($map_action_text, 'next-step'))
                    {
                        // Extract the uid of the next workflowStep from the button's map_action_text
                        $nextStepUid        = explode('::', $map_action_text);
                        $nextStepUid        = $nextStepUid[1];
                        // Use that uid to grab the workflow step that correlates with the template step from our array
                        $nextStep           = $workflowTemplateStepsArray[$nextStepUid]['workflowStep'];
                        // Rebuild the map_action_text to use this template step uid instead
                        $map_action_text    = 'next-step::'.$nextStep->uid;
                    }
                    $workflowStepQuickReply['map_action_text'] = $map_action_text;

                    // Create the WorkflowStepQuickReply
                    $workflowStepQuickReply = QuickReply::create($workflowStepQuickReply);
                    $workflowStepQuickReply->save();

                    // update the buttons mapping
                    $quick_rep_mapping[$workflowTemplateStepQuickReply->uid] = $workflowStepQuickReply->uid;

                    // check if this quick repy has assigned a tag
                    $tag_creation = $this->create_taggable_template ($tags_mapping, 'quick_replies', $workflowStepQuickReply, $workflowTemplateStepQuickReply);

                    if ($tag_creation['error'] == 1)
                    {
                        $response['error']      = 1;
                        $response['error_msg']  = $tag_creation['error_msg'];

                        return $response;
                    }

                    // find out if the quick reply has a custom field assignated
                    $custom_field_template = $workflowTemplateStepQuickReply->customField;
                    if (isset($custom_field_template))
                    {

                        $custom_field_data = [
                                    'validation_type'       => $custom_field_template->validation_type,
                                    'page_uid'              => $page->uid,
                                    'merge_tag'             => $custom_field_template->merge_tag,
                                    'custom_field_type'     => $custom_field_template->custom_field_type,
                                    'default_value'         => $custom_field_template->default_value,
                                    'archived'              => false
                        ];

                        $already_custom_field = $page->customFields()->where('field_name', $custom_field_template->field_name)->first();

                        if( isset($already_custom_field))
                        {
                            // I f the page already has an equal custom field
                            $custom_field_data['field_name']    = $custom_field_template->field_name;
                            $custom_field_data['merge_tag']     = $custom_field_template->merge_tag;
                        }
                        else
                        {
                            $custom_field_data['field_name']    = $custom_field_template->field_name;
                            $custom_field_data['merge_tag']     = $custom_field_template->merge_tag;
                        }

                        $custom_field = \App\CustomField::create($custom_field_data);

                        // Now it's time to update the step item map
                        $workflowStepQuickReply->custom_field_uid = $custom_field->uid;
                        $workflowStepQuickReply->save();
                    }
                }
            }
            elseif( $workflowTemplateStep->step_type == 'delay' )
            {

                $workflowTemplateOptionDelay = $workflowTemplateStep->optionDelay()->first();

                // Get template-repared version of this WorkflowStepOptionDelay
                $workflowOptionDelay = \App\WorkflowTemplateStepOptionDelay::prepareForStepOptionDelay($workflowTemplateOptionDelay, $workflowStep);

                 //\Log::debug(print_r($workflowTemplateStepsArray,1));

                // Let's assign the next uid
                $nextStep = $workflowTemplateStepsArray[$workflowTemplateOptionDelay->workflow_template_next_step_uid]['workflowStep'];
                $nextStepUid = $nextStep->uid;
                $workflowOptionDelay['next_step_uid'] = $nextStepUid;

                // Create the WorkflowStepOptionDelay 
                $workflowOptionDelay = \App\WorkflowStepOptionDelay::create($workflowOptionDelay);
            }
            elseif( $workflowTemplateStep->step_type == 'randomizer')
            {
                
                foreach($workflowTemplateStep->optionRandomizations()->orderBy('uid','asc')->get() as $key2 => $workflowTemplateStepRandom)
                {
                    // Get the template perated version of this workflowStepRandom
                    $workflowStepRandom = \App\WorkflowTemplateStepOptionRandom::prepareForTemplate($workflowTemplateStepRandom, $workflowStep);

                    // Let's assign the next uid
                    $nextStep = $workflowTemplateStepsArray[$workflowTemplateStepRandom->workflow_template_next_step_uid]['workflowStep'];
                    $nextStepUid = $nextStep->uid;
                    $workflowStepRandom['next_step_uid'] = $nextStepUid;

                    // Create the WorkflowTemplateStepItem
                    $workflowStepRandom = \App\WorkflowStepOptionRandom::create($workflowStepRandom);

                }
            }
            elseif( $workflowTemplateStep->step_type == 'conditions')
            {

                foreach($workflowTemplateStep->optionConditions()->orderBy('uid','asc')->get() as $key2 => $workflowTemplateStepCondition)
                {
                    // Get the template perated version of this workflowStepRandom
                    $workflowStepCondition = \App\WorkflowTemplateStepOptionCondition::prepareForTemplate($workflowTemplateStepCondition, 
                                                                                                          $workflowStep,
                                                                                                          $tags_mapping,
                                                                                                          $buttons_mapping,
                                                                                                          $quick_rep_mapping);

                    // Let's assign the next uid
                    $nextStep = $workflowTemplateStepsArray[$workflowTemplateStepCondition->workflow_template_next_step_uid]['workflowStep'];
                    $nextStepUid = $nextStep->uid;
                    $workflowStepCondition['next_step_uid'] = $nextStepUid;

                    // Create the WorkflowTemplateStepItem
                    $workflowStepCondition = \App\WorkflowStepOptionCondition::create($workflowStepCondition);

                    // TODO. Delete tags and subscritions

                }
            }
            elseif( $workflowTemplateStep->step_type == 'sms')
            {

                $workflowTemplateStepSms = $workflowTemplateStep->optionSms()->first();

                // Get template-repared version of this WorkflowSms
                $workflowSms = \App\WorkflowTemplateStepSms::prepareForTemplate($workflowTemplateStepSms, $workflowStep);

                // Create the WorkflowStepSms
                $workflowSms = \App\WorkflowStepSms::create($workflowSms);

            }
            
        }

        if ( isset($this->picture_url) )
        {
            // Get the original file
            $org_image      = file_get_contents($this->picture_url);
            // Generate a new name
            $new_filename   = $workflow->uid.'_'.Str::random(22);
            // Generate the full path
            $full_path      = 'images/'.$new_filename;
            // Upload the file to our storage
            $new_file       = \Storage::disk('media')->put($full_path, $org_image, 'public');
            // Get the URL
            $new_file_url   = \Storage::disk('media')->url($full_path);

            $workflow->picture_url = $new_file_url;

            $workflow->save();
        }

        // Operation completed successfully, commit the database records and return successful response
        \DB::commit();

        $response['success']    = true;
        $response['workflow']   = $workflow;

        return $response;
    }

    /**
     * @param WorkflowTemplate $template
     * @param Page $page
     * @return array
     */
    public static function prepareForWorkflow(WorkflowTemplate $template, Page $page)
    {
        // Create a workflow from the template..

        // Unset the stuff we don't need/will replace
        unset($template->uid);
        unset($template->chatmatic_user_uid);
        unset($template->created_at_utc);
        unset($template->updated_at_utc);
        unset($template->root_workflow_template_step_uid);
        unset($template->origin_workflow_uid);
        unset($template->description);
        unset($template->price);
        unset($template->video_url);
        unset($template->category);
        unset($template->public);
        unset($template->published);

        $workflow = $template->toArray();
        foreach($workflow as $key => $value)
        {
            if(is_array($value))
                unset($workflow[$key]);
        }

        // Set the stuff we need
        $workflow['page_uid'] = $page->uid;
        $workflow['workflow_template_uid'] = $template->uid;

        // Return the array
        return $workflow;
    }

    /**
     * Delete WorkflowTemplate-related records on other tables
     *
     * @throws \Exception
     */
    public function deleteWorkflowTemplateData()
    {
        WorkflowTemplateStepItemImage::where('workflow_template_uid', $this->uid)->delete();
        WorkflowTemplateStepItemMap::where('workflow_template_uid', $this->uid)->delete();
        WorkflowTemplateStepItem::where('workflow_template_uid', $this->uid)->delete();
        WorkflowTemplateStep::where('workflow_template_uid', $this->uid)->delete();
    }

    /**
     * @return mixed
     */
    public function shareCode()
    {
        return \Hashids::connection('templates')->encode($this->uid);
    }

    /**
     * @param $share_code
     * @return WorkflowTemplate|WorkflowTemplate[]|\Illuminate\Database\Eloquent\Collection|Model|null
     */
    public static function findFromShareCode($share_code)
    {
        \Log::info('-------- share code -------');
        \Log::info(\Hashids::connection('templates')->encode('9'));
        return self::find(\Hashids::connection('templates')->decode($share_code))->first();
    }

        private function create_tags_mapping()
    {
        $tags_mapping = [];
        $buttons_tags = $this->workflowTemplateStepItemMaps;
        $qr_tags = $this->workflowTemplateQuickReplies;

        foreach ($buttons_tags as $button) {
            $taggables = \App\TaggableTemplate::where('taggable_template_uid',$button->uid)->where('taggable_type','App\WorkflowStepItemMap')->get();
            foreach ($taggables as $taggable) 
            {
                if (isset($taggable))
                {
                
                    $tags_mapping[$taggable->tag->value]['buttons'][] = $button->uid;
                    $tags_mapping[$taggable->tag->value]['template_tag_uid'] = $taggable->tag_template_uid;
                }    
            }
        }

        foreach ($qr_tags as $quick_reply) {
            $taggables = \App\TaggableTemplate::where('taggable_template_uid',$quick_reply->uid)->where('taggable_type','App\QuickReply')->get();
            foreach ($taggables as $taggable)
            {
                if (isset($taggable))
                {
                    $tags_mapping[$taggable->tag->value]['quick_replies'][] = $quick_reply->uid;
                    $tags_mapping[$taggable->tag->value]['template_tag_uid'] = $taggable->tag_template_uid;
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

                        \App\Taggable::create([
                            'tag_uid' => $tag_map['tag_uid'],
                            'taggable_uid' => $new_element->uid,
                            'taggable_type' => $taggable_type
                        ]);

                    }
                }
            }
            $response['success'] = 1;

        } catch (\Exception $e) {

            $response['error'] = 1;
            $response['error_msg'] = 'Error creating tags from this template '.$e->getMessage();
        }

        return $response;
        
    }
}
