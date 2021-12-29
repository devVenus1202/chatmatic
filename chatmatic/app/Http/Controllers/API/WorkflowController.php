<?php

namespace App\Http\Controllers\API;

use App\AuditLog;
use App\QuickReply;
use App\Workflow;
use App\WorkflowStep;
use App\WorkflowStepItem;
use App\WorkflowStepItemAudio;
use App\WorkflowStepItemImage;
use App\WorkflowStepItemMap;
use App\WorkflowStepItemVideo;
use App\WorkflowStepOptionDelay;
use App\WorkflowStepOptionRandom;
use App\WorkflowStepOptionCondition;
use App\WorkflowStepSms;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Jobs\MediaAttachmentApi;

use Log;

class WorkflowController extends BaseController
{
    /**
     * @param Request $request
     * @param $page_uid
     * @return array
     */
    public function index(Request $request, $page_uid)
    {
        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        $response_array = [
            'workflows'         => [],
            'favorite_step_ids' => [],
            'broadcasts'        => [],
            'merge_tags'        => []
        ];
        foreach($page->workflows()->where('archived','!=','1')->orderBy('uid', 'asc')->get() as $index => $workflow)
        {
            /** @var \App\Workflow $workflow */

            $response_array['workflows'][$index] = [
                'uid'                       => $workflow->uid,
                'page_uid'                  => $workflow->page->uid,
                'name'                      => $workflow->name,
                'picture_url'               => $workflow->picture_url,
                'root_workflow_step_uid'    => $workflow->root_workflow_step_uid,
                'created_at_utc'            => $workflow->created_at_utc->toDateTimeString(),
                'to_json'                   => $workflow->to_json,
                'to_private_rep'            => $workflow->to_private_rep,

            ];

        }

        // Populate favorite steps
        foreach($page->workflowSteps()->where('favorite', 1)->get() as $favorite_step)
        {
            $response_array['favorite_step_uids'][] = $favorite_step->uid;
        }

        // Populate merge tags
        foreach($page->customFields()->orderBy('field_name', 'asc')->get() as $custom_field)
        {
            $response_array['merge_tags'][] = $custom_field->merge_tag;
        }

        $elapsed    = time() - $this->start_time;
        $event_name = 'page.workflows.loaded';

        AuditLog::create([
            'chatmatic_user_uid'    => $this->user->uid,
            'page_uid'              => $page->uid,
            'event'                 => $event_name,
            'message'               => count($response_array['workflows']).' workflows loaded in '.$elapsed.' seconds'
        ]);

        return $response_array;
    }

    /**
     * @param Request $request
     * @param $page_uid
     * @return array
     */
    public function show(Request $request, $page_uid, $workflow_uid)
    {
        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        $response_array = [
            'merge_tags'       => []
        ];

        $workflow = $page->workflows()->where('uid',$workflow_uid)->first();

        $message_count_ratios   = $workflow->generateMessageCountRatios();
        $messages_read_ratio    = $message_count_ratios['read_ratio'];
        $messages_clicked_ratio = $message_count_ratios['clicked_ratio'];

        $response_array['workflow'] = [
                'uid'                       => $workflow->uid,
                'page_uid'                  => $workflow->page->uid,
                'name'                      => $workflow->name,
                'picture_url'               => $workflow->picture_url,
                'created_at_utc'            => $workflow->created_at_utc->toDateTimeString(),
                'steps'                     => [],
        ];


        // Populate steps array
        foreach($workflow->workflowSteps()->orderBy('uid', 'asc')->get() as $steps_index => $workflow_step)
        {
            /** @var \App\WorkflowStep $workflow_step */

            $response_array['workflow']['steps'][$steps_index] = [
                'name'                  => $workflow_step->name,
                'step_uid'              => $workflow_step->uid,
                'type'                  => $workflow_step->step_type,
                'child_uid'            => $workflow_step->child_step_uid,
                'position'              => [
                    'x'         => $workflow_step->x_pos,
                    'y'         => $workflow_step->y_pos,
                ],
            ];

            // Let's add the proper option depending the step type
            if ($workflow_step->step_type == 'items')
            {
                $response_array['workflow']['steps'][$steps_index]['items'] = [];
                $response_array['workflow']['steps'][$steps_index]['quick_replies'] = [];
            }
            else
            {
                $response_array['workflow']['steps'][$steps_index]['options'] = [];
            }

            // Populate quick replies
            $quick_replies = $workflow_step->quickReplies()->orderBy('uid', 'asc')->get();
            foreach($quick_replies as $quick_replies_index => $quick_reply)
            {
                /** @var \App\QuickReply $quick_reply */

                $response_array['workflow']['steps'][$steps_index]['quick_replies'][$quick_replies_index] = [
                    'uid'               => $quick_reply->uid,
                    'reply_type'        => $quick_reply->type,
                    'reply_text'        => $quick_reply->map_text,
                    'tags'              => [],
                    'automation_uid'    => $quick_reply->automation_uid,
                    'next_step_uid'     => (int) str_replace('next-step::', '', $quick_reply->map_action_text),
                    'custom_field_uid'  => $quick_reply->custom_field_uid,
                    'custom_field_value'=> $quick_reply->custom_field_value,
                ];

                // Populate quick reply tags
                $tags = $quick_reply->tags()->get();
                foreach($tags as $tag)
                {
                    /** @var \App\Tag $tag */

                    $response_array['workflow']['steps'][$steps_index]['quick_replies'][$quick_replies_index]['tags'][] = [
                        'uid'   => $tag->uid,
                        'value' => $tag->value
                    ];
                }
            }

            // Populate step optios array
            if ($workflow_step->step_type == 'randomizer')
            {
                foreach($workflow_step->optionRandomizations()->orderBy('uid', 'desc')->get() as $options_index => $workflow_step_option)
                {
                    $options_data = [
                        'uid'               => $workflow_step_option->uid,
                        'option'            => $workflow_step_option->name,
                        'percentage'        => $workflow_step_option->probability,
                        'next_step_uid'     => $workflow_step_option->next_step_uid
                    ];

                    // Attach the array just created to the response array
                    $response_array['workflow']['steps'][$steps_index]['options'][$options_index] = $options_data;

                }
            } 
            else if ($workflow_step->step_type == 'delay')
            {
                $workflow_step_delay = $workflow_step->optionDelay()->first();

                $option_delay = [
                    'uid'                   => $workflow_step_delay->uid,
                    'type'                  => $workflow_step_delay->type,
                    'next_step_uid'         => $workflow_step_delay->next_step_uid,
                ];

                if ($workflow_step_delay->type == 'remaining')
                {
                    $option_delay['time_unit'] = $workflow_step_delay->time_unit;
                    $option_delay['amount'] = $workflow_step_delay->amount;
                }
                else
                {
                    $option_delay['fire_until'] = $workflow_step_delay->fire_until;
                }

                // Attach the array just created to the response array
                $response_array['workflow']['steps'][$steps_index]['options'] = $option_delay;
            }
            else if ($workflow_step->step_type == 'conditions')
            {
                foreach($workflow_step->optionConditions()->orderBy('uid', 'desc')->get() as $options_index => $workflow_step_option)
                {

                    // Let's update conditions to send the tags and tag_uids to th UI
                    $conditions = json_decode($workflow_step_option->conditions_json,true);

                    $tag_keys = ['has_tag','does_not_have_tag'];
                    $subscribed_keys = ['user_subscribed_to','user_not_subscribed_to'];

                    // Iterate for tags
                    foreach ($tag_keys as $key){
                        if (isset($conditions[$key])){
                            $tag_uids = $conditions[$key];
                            unset($conditions[$key]);
                            foreach ($tag_uids as $tag_uid){                                           
                                $tag = $page->tags()->where('uid',$tag_uid)->first();
                                if (isset($tag))
                                {
                                    $conditions[$key][] = [
                                        'uid'   => $tag->uid,
                                        'name' => $tag->value
                                    ];
                                }
                            }
                        }
                    }
                    
                    // Iterate for subscriptions
                    foreach ($subscribed_keys as $key){
                        if (isset($conditions[$key])){
                            $subscriptions = $conditions[$key];
                            unset($conditions[$key]);
                            foreach ($subscriptions as $subscription_uid){
                                /** @var \App\WorkflowTriggers $trigger */
                                $trigger = $page->workflowTriggers()->where('uid',$subscription_uid)->first();
                                if (isset($trigger))
                                {
                                    $conditions[$key][] = [
                                        'uid'   => $trigger->uid,
                                        'name' => $trigger->name
                                    ];
                                }
                            }
                        }
                    }

                    // Asign uid buttons text
                    if(isset($conditions['user_clicked_button']))
                    {
                        $buttons_uids = $conditions['user_clicked_button'];
                        unset($conditions['user_clicked_button']);
                        $workflow_buttons = $workflow->buttons()->get();
                        foreach ($buttons_uids as $button_uid)
                        {
                            foreach ($workflow_buttons as $button) {
                                if ( $button->uid === $button_uid )
                                {
                                    $conditions['user_clicked_button'][] = [
                                        'uid'            => $button->uid,
                                        'name'    => $button->map_text
                                    ];
                                    break;
                                }
                            }
                        }
                    }

                    // Asign uid quick rep text
                    if(isset($conditions['user_clicked_quick_reply']))
                    {
                        $q_reps_uids = $conditions['user_clicked_quick_reply'];
                        unset($conditions['user_clicked_quick_reply']);
                        $workflow_quick_reps = $workflow->workflowQuickReplies()->get();
                        foreach ($q_reps_uids as $q_rep_uid)
                        {
                            foreach ($workflow_quick_reps as $q_rep) {
                                if ( $q_rep->uid === $q_rep_uid )
                                {
                                    $conditions['user_clicked_quick_reply'][] = [
                                        'uid'            => $q_rep->uid,
                                        'name'    => $q_rep->map_text
                                    ];
                                    break;
                                }
                            }
                        }
                    }

                    $options_data = [
                        'uid'               => $workflow_step_option->uid,
                        'option'            => $workflow_step_option->name,
                        'conditions'        => $conditions,
                        'match'             => $workflow_step_option->match,
                        'next_step_uid'     => $workflow_step_option->next_step_uid
                    ];

                    // Attach the array just created to the response array
                    $response_array['workflow']['steps'][$steps_index]['options'][$options_index] = $options_data;

                }
            }
            else if ($workflow_step->step_type == 'sms')
            {
                $workflow_step_sms = $workflow_step->optionSms()->first();

                $option_sms = [
                    'uid'                   => $workflow_step_sms->uid,
                    'sms_text_message'      => $workflow_step_sms->text_message,
                    'phone_number_to'       => $workflow_step_sms->phone_number_to
                ];

                // Attach the array just created to the response array
                $response_array['workflow']['steps'][$steps_index]['options'] = $option_sms;
            }


            // Populate step items array
            foreach($workflow_step->workflowStepItems()->orderBy('item_order', 'asc')->get() as $items_index => $workflow_step_item)
            {
                /** @var \App\WorkflowStepItem $workflow_step_item */

                $step_item_data = [
                    'uid'               => $workflow_step_item->uid,
                    'type'              => $workflow_step_item->item_type, // carousel, text, image, etc
                    'headline'          => $workflow_step_item->headline,
                    'description'       => $workflow_step_item->content,
                    'text_message'      => $workflow_step_item->text_message,
                    'order'             => $workflow_step_item->item_order,
                    'next_step_uid'     => null,
                    'custom_field_uid'  => null,
                ];

                // If this is a delay we'll translate the delay timing
                if($step_item_data['type'] === 'delay')
                {
                    $typing_delay_obj             = json_decode($step_item_data['description']);
                    if(is_object($typing_delay_obj))
                    {
                        $step_item_data['delay_time']   = $typing_delay_obj->delay;
                        $step_item_data['show_typing']  = $typing_delay_obj->typing;
                    }
                    else
                    {
                        // If we have a typing step item in the database but it doesn't parse properly or has empty
                        // values for some reason we'll put the defaults back in
                        $step_item_data['delay_time']   = 4;
                        $step_item_data['show_typing']  = true;
                    }

                    unset($step_item_data['description']);
                    unset($step_item_data['headline']);
                    unset($step_item_data['text_message']);
                }

                // It's a carousel and we'll need to build the steps out of images/button maps
                if($step_item_data['type'] === 'carousel')
                {
                    foreach($workflow_step_item->workflowStepItemImages()->orderBy('uid', 'asc')->get() as $item_image_index => $workflow_step_item_image)
                    {
                        /** @var \App\WorkflowStepItemImage $workflow_step_item_image */

                        $step_item_data['items'][$item_image_index] = [
                            'media_uid'     => $workflow_step_item_image->uid,
                            'headline'      => $workflow_step_item_image->image_title,
                            'description'   => $workflow_step_item_image->image_subtitle,
                            'image'         => $workflow_step_item_image->image_url,
                            'image_order'   => $workflow_step_item_image->image_order,
                            'image_link'    => $workflow_step_item_image->redirect_url,
                        ];

                        // So with carousel images the button mapping should be associated with it via the workflow_step_item_image_uid on the workflow_step_item_map row
                        $buttons        = [];
                        $image_buttons  = $workflow_step_item_image->workflowStepItemMaps()->orderBy('uid', 'asc')->get();
                        foreach($image_buttons as $image_button_index => $image_button)
                        {
                            /** @var \App\WorkflowStepItemMap $image_button */
                            $buttons[$image_button_index] = $image_button->generateButtonArrayForFrontend();
                        }

                        // Attach the buttons to the response array
                        $step_item_data['items'][$item_image_index]['action_btns'] = $buttons;
                    }
                }

                // If it's a free_text_input (custom fields)
                if($workflow_step_item->item_type === 'free_text_input')
                {
                    // Let's get the associated button...
                    $free_text_button = $workflow_step_item->workflowStepItemMaps()->where('map_action', 'input')->first();

                    if($free_text_button)
                    {
                        // Determine the next_step_uid
                        $next_step = $free_text_button->map_action_text;
                        $next_step = str_replace('next-step::', '', $next_step);

                        $step_item_data['custom_field_uid'] = $free_text_button->custom_field_uid;
                        $step_item_data['next_step_uid']    = $next_step;
                        $step_item_data['automation_uid']   = $free_text_button->automation_uid;

                        // Populate tags
                        // TODO: This could be made much more efficient, rather than looping through all the applied tags just pull the uniques
                        $all_tags_on_this_step_item = [];
                        foreach($free_text_button->tags()->get() as $tag)
                        {
                            /** @var \App\Tag $tag */

                            // Create an array of all tags then drop it down to just the uniques

                            if( ! isset($all_tags_on_this_step_item[$tag->uid]))
                            {
                                $all_tags_on_this_step_item[$tag->uid] = [
                                    'uid'   => $tag->uid,
                                    'value' => $tag->value
                                ];
                            }
                        }

                        // Now that we have an array of unique tags we'll drop just those on the step item array
                        foreach($all_tags_on_this_step_item as $tag_on_this_step_item)
                        {
                            $step_item_data['tags'][] = $tag_on_this_step_item;
                        }
                    }
                    else // The button wasn't found - we'll drop in null values for now (this shouldn't happen but has at least once)
                    {
                        $step_item_data['custom_field_uid'] = null;
                        $step_item_data['next_step_uid']    = null;
                        $step_item_data['automation_uid']   = null;
                        $step_item_data['tags']             = [];
                    }
                }

                // Populate image/video/audio/buttons etc
                // If it's not a carousel we'll handle them here
                if($workflow_step_item->item_type !== 'carousel')
                {
                    // Populate the image
                    /** @var \App\WorkflowStepItemImage $step_item_image */
                    if($step_item_image = $workflow_step_item->workflowStepItemImages()->first())
                    {
                        $step_item_data['image']                = $step_item_image->image_url;
                        $step_item_data['image_headline']       = $step_item_image->image_title;
                        $step_item_data['image_description']    = $step_item_image->image_subtitle;
                        $step_item_data['media_uid']            = $step_item_image->uid;
                        $step_item_data['image_link']           = $step_item_image->redirect_url;
                    }

                    // Populate the video
                    /** @var \App\WorkflowStepItemVideo $step_item_video */
                    if($step_item_video = $workflow_step_item->workflowStepItemVideos()->first())
                    {
                        $step_item_data['video']    = $step_item_video->video_url;
                        $step_item_data['media_uid']= $step_item_video->uid;
                    }

                    // Populate the audio
                    /** @var \App\WorkflowStepItemAudio $step_item_audio */
                    if($step_item_audio = $workflow_step_item->workflowStepItemAudios()->first())
                    {
                        $step_item_data['audio']    = $step_item_audio->audio_url;
                        $step_item_data['media_uid']= $step_item_audio->uid;
                    }

                    // So we're handling carousel's in a bit of a messy way, as such the buttons for them are actually associated with each 'pane'
                    // (in this case, represented by a workflow_step_item_image). We'll check here for anything other than a carousel and populate
                    // the buttons if so
                    $buttons            = [];
                    $step_item_buttons  = $workflow_step_item->workflowStepItemMaps()->orderBy('uid', 'asc')->where('map_action', '!=', 'input')->get();
                    foreach($step_item_buttons as $step_item_button_index => $step_item_button)
                    {
                        /** @var \App\WorkflowStepItemMap $step_item_button */
                        $buttons[$step_item_button_index] = $step_item_button->generateButtonArrayForFrontend();
                    }

                    if(count($buttons))
                    {
                        // Attach the buttons to the response array
                        $step_item_data['action_btns'] = $buttons;
                    }
                }

                // Attach the array just created to the response array
                $response_array['workflow']['steps'][$steps_index]['items'][$items_index] = $step_item_data;
            }
        }


        // Populate merge tags
        foreach($page->customFields()->orderBy('field_name', 'asc')->get() as $custom_field)
        {
            $response_array['merge_tags'][] = $custom_field->merge_tag;
        }

        $elapsed    = time() - $this->start_time;
        $event_name = 'page.workflows.loaded';

        AuditLog::create([
            'chatmatic_user_uid'    => $this->user->uid,
            'page_uid'              => $page->uid,
            'event'                 => $event_name,
            'message'               => count($response_array['workflow']).' workflow loaded in '.$elapsed.' seconds'
        ]);

        return $response_array;
    }

    /**
     * @param Request $request
     * @param $page_uid
     * @return array
     * @throws \Exception
     */
    public function create(Request $request, $page_uid)
    {
        $response = [
            'success'       => 0,
            'error'         => 0,
            'error_msg'     => '',
            'uid'           => null,
        ];

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        // Extract request vars
        $workflow_name              = $request->get('name');
        $workflow_steps             = $request->get('steps');
        $picture_url                = $request->get('picture_url');

        // Validate workflow name length
        if(mb_strlen($workflow_name) > 64)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Name provided for workflow is too long, must be 64 characters or less.';

            return $response;
        }

        // Confirm we don't already have a workflow with this name
        $dupe_test = $page->workflows()->where('name', $workflow_name)->first();
        if($dupe_test)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'A workflow with that name already exists.';

            return $response;
        }

        // validate we have a valid url for pictures
        if( $picture_url && ! filter_var($picture_url, FILTER_VALIDATE_URL))
        {
            $response['error']          = 1;
            $response['error_msg']      = 'Please provide a valid url for the picture';

            return $response;
        }


        // If there's no steps throw an error
        if( ! count($workflow_steps))
        {
            $response['error']      = 1;
            $response['error_msg']  = 'No steps provided in workflow.';

            return $response;
        }

        // Start database transaction
        \DB::beginTransaction();

        // Get the initial workflow
        $workflow                   = new Workflow;
        $workflow->name             = $workflow_name;
        $workflow->page_uid         = $page->uid;
        $workflow->picture_url      = $picture_url;

        // Save the workflow
        $saved = $workflow->save();

        if( ! $saved)
        {
            // Error saving workflow
            $response['error']      = 1;
            $response['error_msg']  = 'Error saving workflow';

            // Rollback our database changes
            \DB::rollBack();

            return $response;
        }

        // Workflow is created, let's populate it's steps
        $root_step_is_set   = false;
        $temp_step_uid_map  = [];
        foreach($workflow_steps as $step)
        {
            $workflow_step                          = new WorkflowStep;
            $workflow_step->workflow_uid            = $workflow->uid;
            $workflow_step->step_type_parameters    = '';
            $workflow_step->name                    = str_limit($step['name'], 64) ?? '';
            $workflow_step->page_uid                = $page->uid;
            $workflow_step->x_pos                   = $step['position']['x'];
            $workflow_step->y_pos                   = $step['position']['y'];

            // Allowed step types
            $steps_types_allowed                    = ['items', 'sms', 'delay', 'randomizer', 'conditions'];

            if( ! in_array($step['type'], $steps_types_allowed, true))
            {
                // The type doesn't match, throw an error
                $response['error']      = 1;
                $response['error_msg']  = 'step type mismatch';

                return $response;
            }
            $workflow_step->step_type               = $step['type'];

            // Save the workflow step
            $saved = $workflow_step->save();

            // Update the temporary step uid map with the real uid
            $temp_step_uid_map[$step['step_uid']] = $workflow_step->uid;

            // Assign the root_workflow_step_uid
            if( ! $root_step_is_set)
            {
                $workflow->root_workflow_step_uid = $workflow_step->uid;
                $workflow->save();
                $root_step_is_set = true;
            }

            if( ! $saved)
            {
                // Saving the workflow step failed
                $response['error']      = 1;
                $response['error_msg']  = 'Error saving workflow step with name: '.$step['name'].'.';

                // Rollback our database changes
                \DB::rollBack();

                return $response;
            }
        }

        // Let's iterate again to find out if we have child steps
        foreach($workflow_steps as $step)
        {
            // Let's find out if the step has assigned a child
            if( $step['child_uid'] )
            {
                // We only save on database those which child is ONLY a items type
                if(isset($temp_step_uid_map[$step['child_uid']]))
                {
                    $child_step = WorkflowStep::find($temp_step_uid_map[$step['child_uid']]);
                    $allowed_steps_for_child = array('items','sms');
                    if( in_array($step['type'],$allowed_steps_for_child) )
                    {
                        $workflow_step = WorkflowStep::find($temp_step_uid_map[$step['step_uid']]);
                        $workflow_step->child_step_uid      = $temp_step_uid_map[$step['child_uid']];
                        $workflow_step->save();  
                    }
                }
            }
        }

        // Now that we've created the steps, we'll loop back through and populate the step items
        // Why didn't we do this in the prior loop? Because we needed to populate $temp_step_uid_map first, as those
        // values are used for the button payloads.
        $temp_quick_reply_uid_map  = [];
        $temp_button_uid_map  = [];
        foreach($workflow_steps as $step)
        {
            // Confirm the step can be found
            if( ! isset($temp_step_uid_map[$step['step_uid']]))
            {
                // Workflow step referenced can't be found
                $response['error']      = 1;
                $response['error_msg']  = 'Workflow step referenced not found: 0x01 '.$step['step_uid'].'.';

                // Rollback our database changes
                \DB::rollBack();

                return $response;
            }

            // Retrieve the step
            $step_uid = $temp_step_uid_map[$step['step_uid']];
            $workflow_step = $workflow->workflowSteps()->where('uid', $step_uid)->first();

            if($step['type'] == 'items')
            {

                // Populate the step items
                $step_items = $step['items'];
                if( ! count($step_items))
                {
                    $response['error']      = 1;
                    $response['error_msg']  = 'No step items provided in '.$step['name'].' workflow step.';

                    // Rollback our database changes
                    \DB::rollBack();

                    return $response;
                }

                foreach($step_items as $item)
                {
                    // Build the step item
                    $workflow_step_item = new WorkflowStepItem;
                    $workflow_step_item->workflow_uid       = $workflow->uid;
                    $workflow_step_item->workflow_step_uid  = $workflow_step->uid;
                    $workflow_step_item->page_uid           = $page->uid;
                    $workflow_step_item->item_type          = $item['type'];
                    $workflow_step_item->item_order         = $item['order'];

                    // Set these to empty strings for now, will populate later
                    $workflow_step_item->headline           = '';
                    $workflow_step_item->content            = '';
                    $workflow_step_item->text_message       = '';

                    // The 'text' type messages store their payload/test in the 'text_message' column
                    if($workflow_step_item->item_type === 'text' || $workflow_step_item->item_type === 'free_text_input')
                    {
                        $workflow_step_item->text_message       = $item['text_message'] ?? '';
                    }
                    else
                    {
                        $workflow_step_item->headline           = $item['headline'] ?? '';
                        $workflow_step_item->content            = $item['description'] ?? '';
                    }

                    // Validate the step item types
                    $step_types_allowed = [
                        'card',
                        'image',
                        'carousel',
                        'text',
                        'video',
                        'delay', // Formatted as "{'typing':true,'delay':3}" on 'content' column
                        'audio',
                        'free_text_input',
                    ];
                    if( ! in_array($workflow_step_item->item_type, $step_types_allowed))
                    {
                        // workflow step item type doesn't match allowed types
                        $response['error']      = 1;
                        $response['error_msg']  = 'Workflow step item type mismatch ('.$workflow_step_item->item_type.')';

                        // Rollback our database changes
                        \DB::rollBack();

                        return $response;
                    }

                    // Validate 'headline' length
                    if(mb_strlen($workflow_step_item->headline) > 80)
                    {
                        $response['error'] = 1;
                        $response['error_msg'] = 'The headline value ('.$workflow_step_item->headline.') is too long. Maximum length is 80 characters, this one is '.mb_strlen($workflow_step_item->headline);

                        // Rollback our database changes
                        \DB::rollBack();

                        return $response;
                    }

                    // Save the step item so we can access it's uid
                    $workflow_step_item->save();

                    // Depending on what type of step item this is, let's do some more stuff
                    switch($workflow_step_item->item_type)
                    {
                        case 'card':
                            // The request will come through with a 'media_uid' that correlates with an WorkflowStepItemImage
                            // We need to update that record to include the proper values for uids and
                            $image = WorkflowStepItemImage::find($item['media_uid']);
                            if( ! $image)
                            {
                                // workflow step item image not found
                                $response['error']      = 1;
                                $response['error_msg']  = 'Workflow step item image not found with media_uid: '.$item['media_uid'].'.';

                                // Rollback our database changes
                                \DB::rollBack();

                                return $response;
                            }

                            // Update the image record's values
                            $image->page_uid                = $page->uid;
                            $image->workflow_step_uid       = $workflow_step->uid;
                            $image->workflow_uid            = $workflow->uid;
                            $image->workflow_step_item_uid  = $workflow_step_item->uid;
                            $image->image_title             = $item['headline'];
                            $image->image_subtitle          = $item['description'];
                            $image->redirect_url            = '';
                            if(isset($item['image_link']))
                            {
                                $image->redirect_url        = $item['image_link'];
                            }

                            // Save the image
                            $saved = $image->save();
                            if( ! $saved)
                            {
                                // Error saving/updating image data
                                $response['error']      = 1;
                                $response['error_msg']  = 'Error saving image during card creation, media_uid: '.$item['media_uid'].'.';

                                // Rollback our database changes
                                \DB::rollBack();

                                return $response;
                            }

                            break;

                        case 'image':
                            // The request will come through with a 'media_uid' that correlates with an WorkflowStepItemImage
                            // We need to update that record to include the proper values for uids and
                            $image = WorkflowStepItemImage::find($item['media_uid']);
                            if( ! $image)
                            {
                                // workflow step item image not found
                                $response['error']      = 1;
                                $response['error_msg']  = 'Workflow step item image not found with media_uid: '.$item['media_uid'].'.';

                                // Rollback our database changes
                                \DB::rollBack();

                                return $response;
                            }

                            // Update the image record's values
                            $image->page_uid                = $page->uid;
                            $image->workflow_step_uid       = $workflow_step->uid;
                            $image->workflow_uid            = $workflow->uid;
                            $image->workflow_step_item_uid  = $workflow_step_item->uid;
                            $image->image_title             = $item['headline'] ?? '';
                            $image->image_subtitle          = $item['description'] ?? '';
                            $image->redirect_url            = '';
                            if(isset($item['image_link']))
                            {
                                $image->redirect_url        = $item['image_link'];
                            }

                            // Save the image
                            $saved = $image->save();
                            if( ! $saved)
                            {
                                // Error saving/updating image data
                                $response['error']      = 1;
                                $response['error_msg']  = 'Error saving image during image message creation, media_uid: '.$item['media_uid'].'.';

                                // Rollback our database changes
                                \DB::rollBack();

                                return $response;
                            }

                            break;

                        case 'video':
                            // The request will come through with a 'media_uid' that correlates with an WorkflowStepItemVideo
                            // We need to update that record to include the proper values for uids and
                            $video = WorkflowStepItemVideo::find($item['media_uid']);
                            if( ! $video)
                            {
                                // workflow step item video not found
                                $response['error']      = 1;
                                $response['error_msg']  = 'Workflow step item video not found with media_uid: '.$item['media_uid'].'.';

                                // Rollback our database changes
                                \DB::rollBack();

                                return $response;
                            }

                            // Update the video record's values
                            $video->page_uid                = $page->uid;
                            $video->workflow_step_uid       = $workflow_step->uid;
                            $video->workflow_uid            = $workflow->uid;
                            $video->workflow_step_item_uid  = $workflow_step_item->uid;

                            // Save the video
                            $saved = $video->save();
                            if( ! $saved)
                            {
                                // Error saving/updating image data
                                $response['error']      = 1;
                                $response['error_msg']  = 'Error saving video during video message creation, media_uid: '.$item['media_uid'].'.';

                                // Rollback our database changes
                                \DB::rollBack();

                                return $response;
                            }

                            break;

                        case 'audio':
                            // The request will come through with a 'media_uid' that correlates with an WorkflowStepItemAudio
                            // We need to update that record to include the proper values for uids and
                            $audio = WorkflowStepItemAudio::find($item['media_uid']);
                            if( ! $audio)
                            {
                                // workflow step item audio not found
                                $response['error']      = 1;
                                $response['error_msg']  = 'Workflow step item audio not found with media_uid: '.$item['media_uid'].'.';

                                // Rollback our database changes
                                \DB::rollBack();

                                return $response;
                            }

                            // Update the audio record's values
                            $audio->page_uid                = $page->uid;
                            $audio->workflow_step_uid       = $workflow_step->uid;
                            $audio->workflow_uid            = $workflow->uid;
                            $audio->workflow_step_item_uid  = $workflow_step_item->uid;

                            // Save the audio
                            $saved = $audio->save();
                            if( ! $saved)
                            {
                                // Error saving/updating image data
                                $response['error']      = 1;
                                $response['error_msg']  = 'Error saving audio during audio message creation, media_uid: '.$item['media_uid'].'.';

                                // Rollback our database changes
                                \DB::rollBack();

                                return $response;
                            }

                            break;

                        case 'carousel':
                            $carousel_items = $item['items'];
                            if( ! count($carousel_items))
                            {
                                // No items provided for carousel
                                $response['error']      = 1;
                                $response['error_msg']  = 'No items provided for carousel.';

                                // Rollback our database changes
                                \DB::rollBack();

                                return $response;
                            }

                            // So the carousel items are actually WorkflowStepItemImages that also have WorkflowStepItemMap
                            // (buttons) associated with them (optionally).
                            foreach($carousel_items as $carousel_item)
                            {
                                $carousel_image = WorkflowStepItemImage::find($carousel_item['media_uid']);
                                if( ! $carousel_image)
                                {
                                    // No image found for carousel item
                                    $response['error']      = 1;
                                    $response['error_msg']  = 'No image found for carousel item, media_uid: '.$carousel_item['media_uid'].'.';

                                    // Rollback our database changes
                                    \DB::rollBack();

                                    return $response;
                                }

                                // Update the image's values with what we're provided and it's uids
                                $carousel_image->image_title            = $carousel_item['headline'];
                                $carousel_image->image_subtitle         = $carousel_item['description'];
                                $carousel_image->page_uid               = $page->uid;
                                $carousel_image->workflow_uid           = $workflow->uid;
                                $carousel_image->workflow_step_uid      = $workflow_step->uid;
                                $carousel_image->workflow_step_item_uid = $workflow_step_item->uid;
                                if(isset($carousel_item['image_link']))
                                {
                                    $carousel_image->redirect_url       = $carousel_item['image_link'];
                                }

                                // Save the image - but we'll need to save it again after we create the button record, if there is one
                                // to associate the workflow_step_item_map_uid
                                $saved = $carousel_image->save();
                                if( ! $saved)
                                {
                                    // Error saving/updating image on carousel
                                    $response['error']      = 1;
                                    $response['error_msg']  = 'Error saving carousel image with media_uid: '.$carousel_image->uid.'.';

                                    // Rollback our database changes
                                    \DB::rollBack();

                                    return $response;
                                }

                                // Process the buttons associated with this image/carousel step
                                $carousel_item_buttons = $carousel_item['action_btns'];
                                if(count($carousel_item_buttons))
                                {
                                    // Loop through provided buttons creating them
                                    foreach($carousel_item_buttons as $carousel_item_button)
                                    {
                                        $button = new WorkflowStepItemMap;
                                        $button->page_uid                       = $page->uid;
                                        $button->workflow_uid                   = $workflow->uid;
                                        $button->workflow_step_uid              = $workflow_step->uid;
                                        $button->workflow_step_item_uid         = $workflow_step_item->uid;
                                        $button->workflow_step_item_image_uid   = $carousel_image->uid;
                                        $button->map_text                       = $carousel_item_button['label'];
                                        $button->map_action                     = $carousel_item_button['action_type'];
                                        $button->map_action_text                = ''; // We'll set this just below - depending on the type of button
                                        $button->automation_uid                 = $carousel_item_button['automation_uid'] ?? null;

                                        // If this is a postback we'll want to format the 'map_action_text' column with the next-step::workflow_step->uid
                                        // that correlates with the step_identifier
                                        switch($button->map_action)
                                        {
                                            case 'postback':

                                                // Confirm we can find the step that this button is referencing
                                                if( ! isset($temp_step_uid_map[$carousel_item_button['next_step_uid']]))
                                                {
                                                    // Workflow step this button is referencing can't be found
                                                    $response['error']      = 1;
                                                    $response['error_msg']  = 'Workflow step referenced not found: 0x02 '.$carousel_item_button['next_step_uid'].'.';

                                                    // Rollback our database changes
                                                    \DB::rollBack();

                                                    return $response;
                                                }

                                                // Obtain the uid of the next step by checking the $temp_step_uid_map array
                                                $next_step = $temp_step_uid_map[$carousel_item_button['next_step_uid']];
                                                $button->map_action_text = 'next-step::'.$next_step;
                                                break;

                                            case 'web_url':
                                                $button->map_action_text = $carousel_item_button['open_url'];
                                                break;

                                            case 'phone_number':
                                                $button->map_action_text = $carousel_item_button['phone'];
                                                break;

                                            case 'share':
                                                $button->map_action_text = 'share';
                                                break;
                                        }

                                        // Save the button
                                        $saved = $button->save();
                                        // Update the temporary button uid map with the real uid
                                        $temp_button_uid_map[$carousel_item_button['uid']] = $button->uid;
                                        if( ! $saved)
                                        {
                                            // Error saving/updating button on carousel
                                            $response['error']      = 1;
                                            $response['error_msg']  = 'Error saving button on carousel.';

                                            // Rollback our database changes
                                            \DB::rollBack();

                                            return $response;
                                        }

                                        // Associate tags with the button
                                        $button_tags = $carousel_item_button['tags'];
                                        if(count($button_tags))
                                        {
                                            foreach($button_tags as $button_tag)
                                            {
                                                $button_tag_uid = $button_tag['uid'];

                                                // Confirm the tag exists
                                                $tag = $page->tags()->where('uid', $button_tag_uid)->first();
                                                if( ! $tag)
                                                {
                                                    // Error associating tag, it's not found
                                                    $response['error']      = 1;
                                                    $response['error_msg']  = 'Tag with uid: '.$button_tag_uid.' not found.';

                                                    // Rollback our database changes
                                                    \DB::rollBack();

                                                    return $response;
                                                }

                                                // Associate the tag
                                                $button->tags()->attach($tag->uid);
                                            }
                                        }
                                    }
                                }
                            }

                            break;

                        case 'text':
                            // We don't need to do anything here
                            break;

                        case 'free_text_input':
                            // We'll want to create/link the step_item_map here

                            // Get the next step for the button record we'll insert to facilitate
                            $next_step_uid_from_request = $item['next_step_uid'];
                            // Obtain the uid of the next step by checking the $temp_step_uid_map array
                            if(isset($temp_step_uid_map[$next_step_uid_from_request]))
                            {
                                $next_step = $temp_step_uid_map[$next_step_uid_from_request];
                            }
                            else
                            {
                                // If it's not in the $temp_step_uid_map we'll check that step_uid against all steps in this page's workflows to see if it leads to another workflow's step
                                $next_step = WorkflowStep::where('page_uid', $page->uid)->where('uid', $next_step_uid_from_request)->first();

                                if($next_step === null)
                                {
                                    // Workflow step this free text input is referencing can't be found
                                    $response['error']      = 1;
                                    $response['error_msg']  = 'Workflow step referenced in free text input not found: '.$next_step_uid_from_request.'.';

                                    // Rollback our database changes
                                    \DB::rollBack();

                                    return $response;
                                }

                                $next_step = $next_step->uid;
                            }

                            // Confirm there's a custom_field_uid
                            if( ! isset($item['custom_field_uid']) || $item['custom_field_uid'] < 1)
                            {
                                $response['success']      = 0;
                                $response['error']        = 1;
                                $response['error_msg']    = 'No custom field associated with free text input.';

                                // Rollback our database changes
                                \DB::rollBack();

                                return $response;
                            }

                            // Create a button record that we can execute when the response from the free_text_input is received
                            $map_action_text = 'next-step::'.$next_step;

                            $free_text_button = new WorkflowStepItemMap;
                            $free_text_button->map_action           = 'input';
                            $free_text_button->map_action_text      = $map_action_text;
                            $free_text_button->map_text             = ''; // null not allowed
                            $free_text_button->page_uid             = $page->uid;
                            $free_text_button->workflow_uid         = $workflow->uid;
                            $free_text_button->workflow_step_uid    = $workflow_step->uid;
                            $free_text_button->workflow_step_item_uid = $workflow_step_item->uid;
                            $free_text_button->custom_field_uid     = $item['custom_field_uid'];
                            $free_text_button->automation_uid       = $item['automation_uid'];

                            try{
                                $free_text_button->save();
                            } catch (\Exception $e)
                            {
                                \DB::rollBack();

                                $response['success']      = 0;
                                $response['error']        = 1;
                                $response['error_msg']    = 'Error creating free text input object.';

                                return $response;
                            }

                            // Associate tags with the button
                            $button_tags = $item['tags'];
                            if(count($button_tags))
                            {
                                foreach($button_tags as $button_tag)
                                {
                                    $button_tag_uid = $button_tag['uid'];

                                    // Confirm the tag exists
                                    $tag = $page->tags()->where('uid', $button_tag_uid)->first();
                                    if( ! $tag)
                                    {
                                        // Error associating tag, it's not found
                                        $response['error']      = 1;
                                        $response['error_msg']  = 'Tag with uid: '.$button_tag_uid.' not found.';

                                        // Rollback our database changes
                                        \DB::rollBack();

                                        return $response;
                                    }

                                    // Associate the tag
                                    $free_text_button->tags()->attach($tag->uid);
                                }
                            }

                            break;

                        case 'delay':
                            // Format the delay/typing indicator parameters into the 'content' column as json ("{'typing':true,'delay':3}")
                            $delay_payload = [
                                'typing'    => $item['show_typing'],
                                'delay'     => $item['delay_time'],
                            ];
                            $workflow_step_item->content = json_encode($delay_payload);
                            break;
                    }

                    // Process buttons
                    // As long as it's not a carousel we'll process the buttons
                    if($workflow_step_item->item_type !== 'carousel')
                    {
                        // Process the buttons associated with this step
                        if(isset($item['action_btns']))
                        {
                            $step_item_buttons = $item['action_btns'];

                            // Loop through provided buttons creating them
                            foreach($step_item_buttons as $step_item_button)
                            {

                                // Validate length on button text
                                if(mb_strlen($step_item_button['label']) > 32)
                                {
                                    // Workflow step this button is referencing can't be found
                                    $response['error']      = 1;
                                    $response['error_msg']  = 'Button label "'.$step_item_button['label'].'" is too long. Max length is 32 characters. Please update and try again."';

                                    // Rollback our database changes
                                    \DB::rollBack();

                                    return $response;
                                }

                                $button = new WorkflowStepItemMap;
                                $button->page_uid                       = $page->uid;
                                $button->workflow_uid                   = $workflow->uid;
                                $button->workflow_step_uid              = $workflow_step->uid;
                                $button->workflow_step_item_uid         = $workflow_step_item->uid;
                                $button->map_text                       = str_limit($step_item_button['label'], 32, '');
                                $button->map_action                     = $step_item_button['action_type'];
                                $button->map_action_text                = ''; // We'll set this just below - depending on the type of button
                                $button->automation_uid                 = null;
                                if(isset($step_item_button['automation_uid']))
                                    $button->automation_uid             = $step_item_button['automation_uid'] > 0 ? $step_item_button['automation_uid'] : null;

                                // If this is a postback we'll want to format the 'map_action_text' column with the next-step::workflow_step->uid
                                // that correlates with the workflow_step_uid
                                switch($button->map_action) {
                                    case 'postback':

                                        // Obtain the uid of the next step by checking the $temp_step_uid_map array
                                        if(isset($temp_step_uid_map[$step_item_button['next_step_uid']]))
                                        {
                                            $next_step = $temp_step_uid_map[$step_item_button['next_step_uid']];
                                        }
                                        else
                                        {
                                            // If it's not in the $temp_step_uid_map we'll check that step_uid against all steps in this page's workflows to see if it leads to another workflow's step
                                            $next_step = WorkflowStep::where('page_uid', $page_uid)->where('uid', $step_item_button['next_step_uid'])->first();
                                            if($next_step === null)
                                            {
                                                // Workflow step this button is referencing can't be found
                                                $response['error']      = 1;
                                                $response['error_msg']  = 'Workflow step referenced not found: 0x03 '.$step_item_button['next_step_uid'].'.';

                                                // Rollback our database changes
                                                \DB::rollBack();

                                                return $response;
                                            }
                                            $next_step = $next_step->uid;
                                        }

                                        $button->map_action_text = 'next-step::'.$next_step;
                                        break;

                                    case 'web_url':

                                        // Validate button URL length
                                        if(mb_strlen($step_item_button['open_url']) > 250)
                                        {
                                            $response['error']      = 1;
                                            $response['error_msg']  = 'Button URL is '.mb_strlen($step_item_button['open_url']).' characters, max length is 250. 
                                            Please shorten the URL for the button with the label of: '.$button->map_text;

                                            // Rollback our database changes
                                            \DB::rollBack();

                                            return $response;
                                        }

                                        $button->map_action_text = $step_item_button['open_url'];
                                        break;

                                    case 'phone_number':
                                        $button->map_action_text = $step_item_button['phone'];
                                        break;

                                    case 'share':
                                        $button->map_action_text = 'share'; // Nothing, for now.
                                        break;
                                }

                                // Save the button
                                $saved = $button->save();

                                // Update the temporary button uid map with the real uid
                                $temp_button_uid_map[$step_item_button['uid']] = $button->uid;

                                if( ! $saved)
                                {
                                    // Error saving/updating button on step
                                    $response['error']      = 1;
                                    $response['error_msg']  = 'Error saving button on step.';

                                    // Rollback our database changes
                                    \DB::rollBack();

                                    return $response;
                                }

                                // Associate tags with the button
                                $button_tags = $step_item_button['tags'];
                                if(count($button_tags))
                                {
                                    foreach($button_tags as $button_tag)
                                    {
                                        $button_tag_uid = $button_tag['uid'];

                                        // Confirm the tag exists
                                        $tag = $page->tags()->where('uid', $button_tag_uid)->first();
                                        if( ! $tag)
                                        {
                                            // Error associating tag, it's not found
                                            $response['error']      = 1;
                                            $response['error_msg']  = 'Tag with uid: '.$button_tag_uid.' not found.';

                                            // Rollback our database changes
                                            \DB::rollBack();

                                            return $response;
                                        }

                                        // Associate the tag
                                        $button->tags()->attach($tag->uid);
                                    }
                                }
                            }
                        }
                    }

                    // Save the workflow step item
                    $saved = $workflow_step_item->save();
                    if( ! $saved)
                    {
                        // Error saving workflow step item
                        $response['error']      = 1;
                        $response['error_msg']  = 'Error saving workflow step item - type: '.$workflow_step_item->item_type.'.';

                        // Rollback our database changes
                        \DB::rollBack();

                        return $response;
                    }
                }


                /**
                // Populate the step items
                foreach($step_items as $item)
                {
                    $workflow_step_item_response = WorkflowStepItem::updateOrCreate($item, $workflow_step, $temp_step_uid_map);
                    if($workflow_step_item_response['error'])
                    {
                        // Rollback our database changes
                        \DB::rollBack();

                        return $workflow_step_item_response;
                    }
                }
                */


                // Populate the quick replies
                $quick_replies = $step['quick_replies'];
                if(count($quick_replies))
                {
                    foreach($quick_replies as $quick_reply)
                    {
                        $qr                     = new QuickReply;
                        $qr->page_uid           = $page->uid;
                        $qr->workflow_uid       = $workflow->uid;
                        $qr->workflow_step_uid  = $workflow_step->uid;
                        $qr->automation_uid     = $quick_reply['automation_uid'] ?? null;
                        $qr->type               = $quick_reply['reply_type'];
                        $qr->map_text           = $quick_reply['reply_text'];
                        $qr->map_action         = 'postback';
                        $qr->custom_field_uid   = $quick_reply['custom_field_uid'] ?? null;
                        $qr->custom_field_value = $quick_reply['custom_field_value'] ?? null;

                        if(isset($temp_step_uid_map[$quick_reply['next_step_uid']]))
                        {
                            $next_step = $temp_step_uid_map[$quick_reply['next_step_uid']];
                        }
                        else
                        {
                            $next_step = WorkflowStep::where('page_uid', $page_uid)->where('uid', $quick_reply['next_step_uid'])->first();
                            if($next_step === null)
                            {
                                // Workflow step this quick reply is referencing can't be found
                                $response['error']      = 1;
                                $response['error_msg']  = 'Workflow step referenced in quick reply not found 0x09: '.$quick_reply['next_step_uid'].'.';

                                // Rollback our database changes
                                \DB::rollBack();

                                return $response;
                            }
                            $next_step = $next_step->uid;
                        }
                        $qr->map_action_text    = 'next-step::' . $next_step;

                        $qr->save();

                        // Update the temporary quick reply uid map with the real uid
                        $temp_quick_reply_uid_map[$quick_reply['uid']] = $qr->uid;
                        // Associate tags with the quick reply
                        $qr_tags = $quick_reply['tags'];
                        if(count($qr_tags))
                        {
                            foreach($qr_tags as $qr_tag)
                            {
                                $qr_tag_uid = $qr_tag['uid'];
                                // Confirm the tag exists
                                $tag = $page->tags()->where('uid', $qr_tag_uid)->first();
                                if( ! $tag)
                                {
                                    // Error associating tag, it's not found
                                    $response['error']      = 1;
                                    $response['error_msg']  = 'Tag with uid: '.$qr_tag_uid.' not found.';

                                    // Rollback our database changes
                                    \DB::rollBack();

                                    return $response;
                                }

                                // Associate the tag
                                $qr->tags()->attach($tag->uid);
                            }
                        }
                    }
                }
                
            } 
            if($step['type'] == 'sms')
            {
                $workflow_step_sms_response = WorkflowStepSms::updateOrCreate($step, $workflow_step, $temp_step_uid_map);
                if($workflow_step_sms_response['error'])
                {
                    // Rollback our database changes
                    \DB::rollBack();

                    return $workflow_step_sms_response;
                }

            } 
            elseif($step['type'] == 'delay')
            {

                $workflow_step_delay_response = WorkflowStepOptionDelay::updateOrCreate($step, $workflow_step, $temp_step_uid_map);
                if($workflow_step_delay_response['error'])
                {
                    // Rollback our database changes
                    \DB::rollBack();

                    return $workflow_step_delay_response;
                }
                
            } 
            elseif($step['type'] == 'randomizer') 
            {

                $workflow_step_random_response = WorkflowStepOptionRandom::updateOrCreate($step, $workflow_step, $temp_step_uid_map);
                if($workflow_step_random_response['error'])
                {
                    // Rollback our database changes
                    \DB::rollBack();

                    return $workflow_step_random_response;
                }

            }
            elseif($step['type'] == 'conditions')
            {
                if ($workflow_step->step_type == 'conditions')
                {
                    $workflow_step_cond_response = WorkflowStepOptionCondition::updateOrCreate($step, $workflow_step, $temp_step_uid_map, $temp_quick_reply_uid_map,$temp_button_uid_map);
                    if($workflow_step_cond_response['error'])
                    {
                        // Rollback our database changes
                        \DB::rollBack();

                        return $workflow_step_cond_response;
                    }
                }
            }
        }


        // Restictions apply only to the firts step
        $first_step = $workflow_steps[0];
        $restrictions = Workflow::validateRestrictions($first_step);

        // Update workflow restrictions for triggers
        $workflow->to_json               = $restrictions['to_json'];
        $workflow->to_private_rep        = $restrictions['to_private_rep'];
        $workflow->save();
        $this->dispatch(new MediaAttachmentApi($workflow->uid));
        
        // If we got here we're successful and we'll want to set the return data as such
        $response['success'] = 1;
        $response['uid'] = $workflow->uid;

        // Commit transaction to database
        \DB::commit();

        return $response;
    }

    /**
     * @param Request $request
     * @param $page_uid
     * @param $workflow_uid
     * @return array
     */
    public function delete(Request $request, $page_uid, $workflow_uid)
    {
        $response = [
            'success'       => 0,
            'error'         => 0,
            'error_msg'     => '',
        ];

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        $workflow = $page->workflows()->where('uid', $workflow_uid)->first();

        if( ! $workflow)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Workflow not found';

            return $response;
        }

        // Check if there are any active workflow triggers that this workflow is associated with
        /** @var \App\WorkflowTrigger $workflow_trigger */
        
        //$workflow_triggers = $workflow->workflowTriggers()->get();

        $workflow->archived = true;
        $workflow->save();

        $response['success'] = 1;

        return $response;
    }

    /**
     * @param Request $request
     * @param $page_uid
     * @param $workflow_uid
     * @return array
     * @throws \Exception
     */
    public function updatePicture(Request $request, $page_uid, $workflow_uid)
    {
        $response = [
            'success'       => 0,
            'error'         => 0,
            'error_msg'     => '',
            'uid'           => null,
        ];

        // Start a database transaction
        \DB::beginTransaction();

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        // Extract request vars
        $picture_url                = $request->get('picture_url');


        // Get the workflow
        /** @var \App\Workflow $workflow */
        $workflow = $page->workflows()->where('uid', $workflow_uid)->first();

        if( ! $workflow)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Workflow not found';
            
            return $response;
        }

        // Validate we have a picture url
        if (! isset($picture_url) )
        {
            $response['error']      = 1;
            $response['error_msg']  = 'Please provide a picture url';

            return $response;
        }

        // validate we have a valid url for pictures
        if( ! filter_var($picture_url, FILTER_VALIDATE_URL))
        {
            $response['error']          = 1;
            $response['error_msg']      = 'Please provide a valid url for the picture';

            return $response;
        }

        // Update the name
        $workflow->picture_url      = $picture_url;

        $workflow->save();

        $response['success'] = 1;

        // Commit transaction to database
        \DB::commit();

        return $response;
    }

    /**
     * @param Request $request
     * @param $page_uid
     * @param $workflow_uid
     * @return array
     * @throws \Exception
     */
    public function update(Request $request, $page_uid, $workflow_uid)
    {
        $response = [
            'success'       => 0,
            'error'         => 0,
            'error_msg'     => '',
            'uid'           => null,
        ];

        // Start a database transaction
        \DB::beginTransaction();

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        // Extract request vars
        $workflow_name              = $request->get('name');
        $workflow_steps             = $request->get('steps');

        // Validate workflow name length
        if(mb_strlen($workflow_name) > 64)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Name provided for workflow is too long, must be 64 characters or less.';

            \DB::rollBack();
            return $response;
        }

        // Confirm we don't already have a workflow with such name
        $dupe_test = $page->workflows()->where('name', $workflow_name)->where('uid', '!=', $workflow_uid)->first();
        if($dupe_test)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'A workflow with that name already exists.';

            \DB::rollBack();
            return $response;
        }

        // If there's no steps throw an error
        if( ! count($workflow_steps))
        {
            $response['error']      = 1;
            $response['error_msg']  = 'No steps provided in workflow.';

            \DB::rollBack();
            return $response;
        }

        // Get the workflow
        /** @var \App\Workflow $workflow */
        $workflow = $page->workflows()->where('uid', $workflow_uid)->first();

        if( ! $workflow)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Workflow not found';

            \DB::rollBack();
            return $response;
        }

        // Update the name
        $workflow->name             = $workflow_name;

        // Get array of pre-existing steps
        $pre_existing_workflow_steps = $workflow->workflowSteps()->get();

        // Loop through the array of pre existing workflow steps to identify which ones do and do not still exist
        $root_workflow_uid_changed = false;
        foreach($pre_existing_workflow_steps as $pre_existing_workflow_step)
        {
            // See if this step still exists in the step list provided in the request
            $workflow_step_found = false;
            foreach($workflow_steps as $workflow_step)
            {
                // First check to see that there is a step_uid and it's an integer (if it's a string it's a new step)
                if(isset($workflow_step['step_uid']) && is_int($workflow_step['step_uid']))
                {
                    // Check to see if the step_uid matches our $pre_existing_workflow_step->uid
                    if($workflow_step['step_uid'] === $pre_existing_workflow_step->uid)
                    {
                        $workflow_step_found = true;
                    }
                }
            }

            // If the pre_existing_workflow_step wasn't found in the array of workflow steps provided that means it's been
            // removed from the workflow and we should delete it.
            if( ! $workflow_step_found)
            {
                // Check to see if the root_workflow_step_uid gets removed, if so we'll need to set another one later
                if($workflow->root_workflow_step_uid === $pre_existing_workflow_step->uid)
                {
                    $root_workflow_uid_changed = true;
                }
                // Commenting this out for now, need to speak w travis about it
                // It's been removed from the workflow, but should we delete it for good? If it's no longer triggered, what's it matter?
                // That way it's still available to engagements where it's out and potential to be triggered
                // $pre_existing_workflow_step->deleteWithChildren();
                $pre_existing_workflow_step->delete();
            }
        }

        // Now we'll loop through the workflow_steps that were provided in the request, updating the existing and creating the new
        $temp_step_uid_map  = [];
        foreach($workflow_steps as $step)
        {
            // Validate the name value of the workflow step
            if(isset($step['name']) && mb_strlen($step['name']) > 64)
            {
                $response['error']      = 1;
                $response['error_msg']  = 'Engagement Step "'.$step['name'].'" has a name of '.mb_strlen($step['name']).' characters, but only up to 64 is accepted. Please update and try again.';

                \DB::rollBack();
                return $response;
            }

            // If it's a pre-existing workflow step it will have an int uid
            if(is_int($step['step_uid']))
            {
                $workflow_step = $workflow->workflowSteps()->where('uid', $step['step_uid'])->first();

                // We're seeing an issue where this workflow step isn't found, log it for now
                if( ! $workflow_step)
                {
                    $response['error']      = 1;
                    $response['error_msg']  = 'Engagement Step "'.$step['name'].'"/'.$step['step_uid'].' is no longer found on this Engagement so we can\'t save it with this request.';

                    \DB::rollBack();
                    return $response;
                }

                // Update the name
                if($workflow_step->name !== $step['name'] && $step['name'] !== null)
                {
                    $workflow_step->name = $step['name'];
                    $workflow_step->save();
                }

                // Update the step x position
                if($workflow_step->x_pos !== $step['position']['x'])
                {
                    $workflow_step->x_pos = $step['position']['x'];
                    $workflow_step->save();
                }

                // Update the step y position
                if($workflow_step->y_pos !== $step['position']['y'])
                {
                    $workflow_step->y_pos = $step['position']['y'];
                    $workflow_step->save();
                }

                $temp_step_uid_map[$step['step_uid']] = $workflow_step->uid;
            }
            else // string uid means it's a new workflow step
            {
                $workflow_step                          = new WorkflowStep;
                $workflow_step->workflow_uid            = $workflow->uid;
                $workflow_step->step_type               = '';
                $workflow_step->step_type_parameters    = '';
                $workflow_step->name                    = str_limit($step['name'], 64) ?? '';
                $workflow_step->page_uid                = $page->uid;
                $workflow_step->x_pos                   = $step['position']['x'];
                $workflow_step->y_pos                   = $step['position']['y'];

                // Allowed step types
                $steps_types_allowed                    = ['items', 'sms', 'delay', 'randomizer', 'conditions'];

                if( ! in_array($step['type'], $steps_types_allowed, true))
                {
                    // The type doesn't match, throw an error
                    $response['error']      = 1;
                    $response['error_msg']  = 'step type mismatch';

                    return $response;
                }
                $workflow_step->step_type               = $step['type'];


                // Save the workflow step
                $saved = $workflow_step->save();

                // Update the temporary step uid map with the real uid
                $temp_step_uid_map[$step['step_uid']] = $workflow_step->uid;

                if( ! $saved)
                {
                    // Saving the workflow step failed
                    $response['error']      = 1;
                    $response['error_msg']  = 'Error saving workflow step with name: '.$step['name'].'.';

                    // Rollback our database changes
                    \DB::rollBack();

                    return $response;
                }
            }
        }

        // Let's iterate again to find out if we have child steps
        foreach($workflow_steps as $step)
        {
            // Let's find out if the step has assigned a child
            if( $step['child_uid'] )
            {

                // We only save on database those which ONLY are items type
                if(isset($temp_step_uid_map[$step['child_uid']]))
                {
                    $child_step = WorkflowStep::find($temp_step_uid_map[$step['child_uid']]);
                    $allowed_steps_for_child = array('items','sms');
                    if( in_array($step['type'],$allowed_steps_for_child) )
                    {
                        $workflow_step = WorkflowStep::find($temp_step_uid_map[$step['step_uid']]);
                        $workflow_step->child_step_uid      = $temp_step_uid_map[$step['child_uid']];
                        $workflow_step->save();  
                    }
                }
            }
            else if(is_int($step['step_uid'])){
                // Let's check if this is a step that we already have on our database
                $workflow_step = $workflow->workflowSteps()->where('uid', $step['step_uid'])->first();
                // Ensure this is a step that before had a child assigned
                if( $workflow_step->child_step_uid)
                {
                    $workflow_step->child_step_uid = null;
                    $workflow_step->save();
                }
            }
        }

        // Let's add the root steps of active workflows to $temp_step_uid_map,latter we'll be validating the next stap uid
        $active_workflows = $page->workflows()->where('archived', false)->get();
        foreach($active_workflows as $active_workflow)
        {
            // let's apend the data
            $temp_step_uid_map[$active_workflow->root_workflow_step_uid] = $active_workflow->root_workflow_step_uid;
        }

        // Now that we've looped through and created the new workflow_steps and populated the $temp_step_uid_map we'll
        // loop back through
        $temp_quick_reply_uid_map  = [];
        $temp_button_uid_map  = [];
        foreach($workflow_steps as $step)
        {
            // We've already updated the steps themselves, or created them, now we want to update/create their items
            /** @var \App\WorkflowStep $workflow_step */
            $step_uid       = $temp_step_uid_map[$step['step_uid']];
            $workflow_step  = $workflow->workflowSteps()->where('uid', $step_uid)->first();

            if( ! $workflow_step)
            {
                $response['error'] = 1;
                $response['error_msg'] = 'No workflow step found with uid: '.$step_uid;

                // Rollback our database changes
                \DB::rollBack();

                return $response;
            }

            // Let's check item steps
            if ($workflow_step->step_type == 'items')
            {
                // Create array of the step items provided in the request
                $step_items = $step['items'];
                if( ! count($step_items))
                {
                    $response['error']      = 1;
                    $response['error_msg']  = 'No step items provided in '.$step['name'].' workflow step.';

                    // Rollback our database changes
                    \DB::rollBack();

                    return $response;
                }

                // Let's loop through the existing step items to identify those that are no longer existing
                $pre_existing_workflow_step_items = $workflow_step->workflowStepItems()->get();
                foreach($pre_existing_workflow_step_items as $pre_existing_workflow_step_item)
                {
                    // See if this step item still exists in the step item list provided in the request
                    $workflow_step_item_found = false;
                    foreach($step_items as $step_item)
                    {
                        // First check to see that there is a uid and it's an integer
                        if(isset($step_item['uid']))
                        {
                            // Check to see if the step_uid matches our $pre_existing_workflow_step->uid
                            if($step_item['uid'] === $pre_existing_workflow_step_item->uid)
                            {
                                $workflow_step_item_found = true;
                            }
                        }
                    }

                    // If the pre_existing_workflow_step_item wasn't found in the array of workflow steps items provided
                    // that means it's been removed from the workflow and we should delete it.
                    if( ! $workflow_step_item_found)
                    {
                        $pre_existing_workflow_step_item->deleteWithChildren();
                    }
                }

                foreach($step_items as $item_key => $item)
                {
                    // Make sure the free_text_input (custom input) steps items are the last in a step
                    if($item['type'] === 'free_text_input' && $item_key + 1 !== count($step_items))
                    {
                        $response['error']      = 1;
                        $response['error_msg']  = 'User Input step items must be the last item in an engagement step.';

                        // Rollback our database changes
                        \DB::rollBack();
                    }

                    $workflow_step_item_response = WorkflowStepItem::updateOrCreate($item, $workflow_step, $temp_step_uid_map, $temp_button_uid_map);
                    if($workflow_step_item_response['error'])
                    {
                        // Rollback our database changes
                        \DB::rollBack();

                        return $workflow_step_item_response;
                    }
                    $temp_button_uid_map = $workflow_step_item_response['temp_button_uid_map'];
                }
                // Populate the quick replies
                $quick_replies = $step['quick_replies'];
                if(count($quick_replies))
                {
                    // Let's loop through the currently existing quick replies to see if any need to be removed
                    // Note: Do this before creating/updating below, else the new ones will be removed.
                    $pre_existing_workflow_step_quick_replies = $workflow_step->quickReplies()->get();
                    foreach($pre_existing_workflow_step_quick_replies as $pre_existing_workflow_step_quick_reply)
                    {
                        $quick_reply_still_in_use = false;
                        // Loop through all of the QR's provided in the request...
                        foreach($quick_replies as $quick_reply)
                        {
                            // If there's a uid on one of these from the request that means it was pre-existing and we should
                            // set the flag that it's still in use
                            if(isset($quick_reply['uid']) && $quick_reply['uid'] === $pre_existing_workflow_step_quick_reply->uid)
                            {
                                $quick_reply_still_in_use = true;
                            }
                        }

                        // Quick reply is no longer in use, delete it
                        if( ! $quick_reply_still_in_use)
                        {
                            \Log::debug('Deleting quick reply with uid: '.$pre_existing_workflow_step_quick_reply->uid);
                            $pre_existing_workflow_step_quick_reply->delete();
                        }
                    }

                    // We have quick replies in the payloads, let's determine what to add/update/remove
                    foreach($quick_replies as $quick_reply)
                    {
                        if(isset($quick_reply['uid']) && is_numeric($quick_reply['uid']))
                        {
                            $qr = $workflow_step->quickReplies()->where('uid', $quick_reply['uid'])->first();
                        }
                        else
                        {
                            $qr                     = new QuickReply;
                            $qr->page_uid           = $page->uid;
                            $qr->workflow_uid       = $workflow->uid;
                            $qr->workflow_step_uid  = $workflow_step->uid;
                        }

                        $qr->custom_field_uid   = $quick_reply['custom_field_uid'] ?? null;
                        $qr->custom_field_value = $quick_reply['custom_field_value'] ?? null;
                        $qr->automation_uid     = $quick_reply['automation_uid'] ?? null;
                        $qr->type               = $quick_reply['reply_type'];
                        $qr->map_text           = $quick_reply['reply_text'];
                        $qr->map_action         = 'postback';

                        if(isset($temp_step_uid_map[$quick_reply['next_step_uid']]))
                        {
                            $next_step = $temp_step_uid_map[$quick_reply['next_step_uid']];
                        }
                        else
                        {
                            $next_step = WorkflowStep::where('page_uid', $page_uid)->where('uid', $quick_reply['next_step_uid'])->first();
                            if($next_step === null)
                            {
                                // Workflow step this quick reply is referencing can't be found
                                $response['error']      = 1;
                                $response['error_msg']  = 'Workflow step referenced in quick reply not found 0x09: '.$quick_reply['next_step_uid'].'.';

                                // Rollback our database changes
                                \DB::rollBack();

                                return $response;
                            }
                            $next_step = $next_step->uid;
                        }
                        $qr->map_action_text    = 'next-step::' . $next_step;

                        $qr->save();
                        // Update the temporary quick reply uid map with the real uid
                        $temp_quick_reply_uid_map[$quick_reply['uid']] = $qr->uid;

                        // Associate tags with the quick reply
                        $qr_tags = $quick_reply['tags'];
                        if(count($qr_tags))
                        {
                            $sync_tags = [];
                            foreach($qr_tags as $qr_tag)
                            {
                                $qr_tag_uid = $qr_tag['uid'];
                                // Confirm the tag exists
                                $tag = $page->tags()->where('uid', $qr_tag_uid)->first();
                                if( ! $tag)
                                {
                                    // Error associating tag, it's not found
                                    $response['error']      = 1;
                                    $response['error_msg']  = 'Tag with uid: '.$qr_tag_uid.' not found.';

                                    // Rollback our database changes
                                    \DB::rollBack();

                                    return $response;
                                }

                                // Add tag to array of tags to associate
                                $sync_tags[] = $tag->uid;
                            }

                            if(count($sync_tags))
                            {
                                // Remove all existing tags
                                $qr->tags()->sync([]);
                                // Apply supplied tags
                                $qr->tags()->sync($sync_tags);
                            }
                        }
                    }
                }
                else
                {
                    // There are no quick replies provided - let's remove any existing
                    $pre_existing_workflow_step_quick_replies = $workflow_step->quickReplies()->get();
                    if($pre_existing_workflow_step_quick_replies)
                    {
                        foreach($pre_existing_workflow_step_quick_replies as $pre_existing_workflow_step_quick_reply)
                        {
                            $pre_existing_workflow_step_quick_reply->delete();
                        }
                    }
                }                
            }
            else if ($workflow_step->step_type == 'delay')
            {
                $workflow_step_delay_response = WorkflowStepOptionDelay::updateOrCreate($step, $workflow_step, $temp_step_uid_map);
                if($workflow_step_delay_response['error'])
                {
                    // Rollback our database changes
                    \DB::rollBack();

                    return $workflow_step_delay_response;
                }

            }
            else if ($workflow_step->step_type == 'randomizer')
            {
                $workflow_step_random_response = WorkflowStepOptionRandom::updateOrCreate($step, $workflow_step, $temp_step_uid_map);
                if($workflow_step_random_response['error'])
                {
                    // Rollback our database changes
                    \DB::rollBack();

                    return $workflow_step_random_response;
                }
            }
            else if ($workflow_step->step_type == 'conditions')
            {
                $workflow_step_cond_response = WorkflowStepOptionCondition::updateOrCreate($step, $workflow_step, $temp_step_uid_map,$temp_quick_reply_uid_map,$temp_button_uid_map);
                if($workflow_step_cond_response['error'])
                {
                    // Rollback our database changes
                    \DB::rollBack();

                    return $workflow_step_cond_response;
                }
            }
            else if ($workflow_step->step_type == 'sms')
            {
                $workflow_step_sms_response = WorkflowStepSms::updateOrCreate($step, $workflow_step, $temp_step_uid_map);
                if($workflow_step_sms_response['error'])
                {
                    // Rollback our database changes
                    \DB::rollBack();

                    return $workflow_step_sms_response;
                }
            }
            

        }


        // If the root workflow uid has changed/been removed we'll need to update it
        if($root_workflow_uid_changed)
        {
            // If the first step provided in the request isn't an int that means it's one of the new workflow steps and we'll
            // need to get it's real uid from the $temp_step_uid_map array
            if( ! is_int($workflow_steps[0]['step_uid']))
                $new_root_workflow_step_uid = $temp_step_uid_map[$workflow_steps[0]['step_uid']];
            else
                $new_root_workflow_step_uid = $workflow_steps[0]['step_uid'];
            $workflow->root_workflow_step_uid = $new_root_workflow_step_uid;
        }

        // Restictions apply only to the firts step
        $first_step = $workflow_steps[0];
        $restrictions = Workflow::validateRestrictions($first_step);

        // Update workflow restrictions for triggers
        $workflow->to_json               = $restrictions['to_json'];
        $workflow->to_private_rep        = $restrictions['to_private_rep'];
        $workflow->save();
        $this->dispatch(new MediaAttachmentApi($workflow->uid));

        $response['success'] = 1;

        // Commit transaction to database
        \DB::commit();

        return $response;
    }

    /**
     * @param Request $request
     * @param $page_uid
     * @param $workflow_uid
     * @return array
     */
    public function stats(Request $request, $page_uid, $workflow_uid)
    {
        $response = [
            'success'       => 0,
            'error'         => 0,
            'error_msg'     => '',
            'data'          => [],
        ];

        $page = $this->getPage($page_uid);
        $page = $page['page'];

        $workflow = $page->workflows()->where('uid', $workflow_uid)->first();

        $stats = $workflow->generateStatsForUI();

        $response['success'] = 1;
        $response['data'] = $stats;

        return $response;
    }

    public function statistics(Request $request, $page_uid, $workflow_uid)
    {
        $response = [
            'success'       => 0,
            'error'         => 0,
            'error_msg'     => '',
            'data'          => [],
        ];

        $page = $this->getPage($page_uid);
        $page = $page['page'];

        $workflow = $page->workflows()->where('uid', $workflow_uid)->first();

        $statistics = $workflow->generateStatisticsForUI();

        $response['success'] = 1;
        $response['data'] = $statistics;

        return $response;

    }

}
