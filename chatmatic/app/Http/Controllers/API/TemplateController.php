<?php

namespace App\Http\Controllers\API;

use App\Jobs\PushTemplateToNewWorkflow;
use App\Workflow;
use App\WorkflowTemplate;
use Hashids\Hashids;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Mail\TemplatePurchase;
use App\Mail\TravisNewPublicTemplateNotification;
use Illuminate\Support\Facades\Mail;
use App\Tag;
use App\TagTemplate;
use App\WorkflowTrigger;
use App\AppSumoClonedTemplate;
use Carbon\Carbon;

class TemplateController extends BaseController
{

    /**
     * @param Request $request
     * @return array
     */
    public function index(Request $request)
    {
        $response = [
            'success'                   => 0,
            'error'                     => 0,
            'error_msg'                 => 0,
            'templates'                 => [],
        ];

        /** @var \App\User $user */
        $user = $this->user;

        /** @var \App\WorkflowTemplate $template */
        $templates = $user->templates()->where('archived',false)->get();
        foreach($templates as $template)
        {
            $uses_count = Workflow::where('workflow_template_uid', $template->uid)->count();

            $response['templates'][] = [
                'uid'           => $template->uid,
                'name'          => $template->name,
                'price'         => $template->price,
                'category'      => $template->category,
                'public'        => $template->public,
                'description'   => $template->description,
                'downloads'     => $uses_count,
                'share_code'    => \Hashids::connection('templates')->encode($template->uid),
                'workflow_uid'  => $template->origin_workflow_uid
            ];
        }

        $response['success'] = 1;

        return $response;
    }

    /**
     * Show the workflow data to a preview
     *
     * @param Request $request
     * @param $page_uid
     * @param $template_uid
     * @return \App\Page|array
     * @throws \Exception
     */
    public function show(Request $request, $page_uid, $template_uid)
    {
        $response_array = [
            'success'                   => 0,
            'error'                     => 0,
            'error_msg'                 => 0,
            'template'                  => [],
        ];

        /** @var \App\User $user */
        $user = $this->user;

        // $page = $this->getPage($page_uid);
        // if($page['error'] === 1)
        // {
        //     return $page;
        // }
        /** @var \App\Page $page */
        // $page = $page['page'];

        $template = WorkflowTemplate::find($template_uid);

        if ( ! isset($template) )
        {
                $response['error'] = 1;
                $response['error_msg'] = 'The template with the uid '.$template_uid.' does not exist';

            return $response;
        }

        $response_array['name']                 = $template->name;
        $response_array['description']          = $template->description;
        $response_array['price']                = $template->price;
        $response_array['category']             = $template->category;

        // Populate steps array
        foreach($template->workflowTemplateSteps()->orderBy('uid', 'asc')->get() as $steps_index => $workflow_step)
        {
            /** @var \App\WorkflowStep $workflow_step */

            $response_array['template']['steps'][$steps_index] = [
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
                $response_array['template']['steps'][$steps_index]['items'] = [];
                $response_array['template']['steps'][$steps_index]['quick_replies'] = [];
            }
            else
            {
                $response_array['template']['steps'][$steps_index]['options'] = [];
            }

            // Populate quick replies
            $quick_replies = $workflow_step->workflowTemplateStepQuickReplies()->orderBy('uid', 'asc')->get();
            foreach($quick_replies as $quick_replies_index => $quick_reply)
            {
                /** @var \App\QuickReply $quick_reply */

                $response_array['template']['steps'][$steps_index]['quick_replies'][$quick_replies_index] = [
                    'uid'               => $quick_reply->uid,
                    'reply_type'        => $quick_reply->type,
                    'reply_text'        => $quick_reply->map_text,
                    'tags'              => [],
                    'automation_uid'    => $quick_reply->automation_uid,
                    'next_step_uid'     => (int) str_replace('next-step::', '', $quick_reply->map_action_text),
                    'custom_field_uid'  => $quick_reply->custom_field_uid,
                    'custom_field_value'=> $quick_reply->custom_field_value,
                ];

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
                        'next_step_uid'     => $workflow_step_option->workflow_template_next_step_uid
                    ];

                    // Attach the array just created to the response array
                    $response_array['template']['steps'][$steps_index]['options'][$options_index] = $options_data;

                }
            } 
            else if ($workflow_step->step_type == 'delay')
            {
                $workflow_step_delay = $workflow_step->optionDelay()->first();

                $option_delay = [
                    'uid'                   => $workflow_step_delay->uid,
                    'type'                  => $workflow_step_delay->type,
                    'next_step_uid'         => $workflow_step_delay->workflow_template_next_step_uid,
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
                $response_array['template']['steps'][$steps_index]['options'] = $option_delay;
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
                                $tag = TagTemplate::where('uid',$tag_uid)->first();
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
                                $trigger = WorkflowTrigger::where('uid',$subscription_uid)->first();
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

                    $options_data = [
                        'uid'               => $workflow_step_option->uid,
                        'option'            => $workflow_step_option->name,
                        'conditions'        => $conditions,
                        'match'             => $workflow_step_option->match,
                        'next_step_uid'     => $workflow_step_option->workflow_template_next_step_uid
                    ];

                    // Attach the array just created to the response array
                    $response_array['template']['steps'][$steps_index]['options'][$options_index] = $options_data;

                }
            }
            else if ($workflow_step->step_type == 'sms')
            {
                $workflow_step_sms = $workflow_step->optionSms()->first();

                $option_sms = [
                    'uid'                   => $workflow_step_sms->uid,
                    'sms_text_message'      => $workflow_step_sms->text_message
                ];

                // Attach the array just created to the response array
                $response_array['template']['steps'][$steps_index]['options'] = $option_sms;
            }


            // Populate step items array
            foreach($workflow_step->workflowTemplateStepItems()->orderBy('item_order', 'asc')->get() as $items_index => $workflow_step_item)
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
                    foreach($workflow_step_item->workflowTemplateStepItemImages()->orderBy('uid', 'asc')->get() as $item_image_index => $workflow_step_item_image)
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
                        $image_buttons  = $workflow_step_item_image->workflowTemplateStepItemMaps()->orderBy('uid', 'asc')->get();
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
                    $free_text_button = $workflow_step_item->workflowTemplateStepItemMaps()->where('map_action', 'input')->first();

                    if($free_text_button)
                    {
                        // Determine the next_step_uid
                        $next_step = $free_text_button->map_action_text;
                        $next_step = str_replace('next-step::', '', $next_step);

                        $step_item_data['custom_field_uid'] = $free_text_button->custom_field_uid;
                        $step_item_data['next_step_uid']    = $next_step;
                        $step_item_data['automation_uid']   = $free_text_button->automation_uid;
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
                    if($step_item_image = $workflow_step_item->workflowTemplateStepItemImages()->first())
                    {
                        $step_item_data['image']                = $step_item_image->image_url;
                        $step_item_data['image_headline']       = $step_item_image->image_title;
                        $step_item_data['image_description']    = $step_item_image->image_subtitle;
                        $step_item_data['media_uid']            = $step_item_image->uid;
                        $step_item_data['image_link']           = $step_item_image->redirect_url;
                    }

                    // Populate the video
                    /** @var \App\WorkflowStepItemVideo $step_item_video */
                    if($step_item_video = $workflow_step_item->workflowTemplateStepItemVideos()->first())
                    {
                        $step_item_data['video']    = $step_item_video->video_url;
                        $step_item_data['media_uid']= $step_item_video->uid;
                    }

                    // Populate the audio
                    /** @var \App\WorkflowStepItemAudio $step_item_audio */
                    if($step_item_audio = $workflow_step_item->workflowTemplateStepItemAudios()->first())
                    {
                        $step_item_data['audio']    = $step_item_audio->audio_url;
                        $step_item_data['media_uid']= $step_item_audio->uid;
                    }

                    // So we're handling carousel's in a bit of a messy way, as such the buttons for them are actually associated with each 'pane'
                    // (in this case, represented by a workflow_step_item_image). We'll check here for anything other than a carousel and populate
                    // the buttons if so
                    $buttons            = [];
                    $step_item_buttons  = $workflow_step_item->workflowTemplateStepItemMaps()->orderBy('uid', 'asc')->where('map_action', '!=', 'input')->get();
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
                $response_array['template']['steps'][$steps_index]['items'][$items_index] = $step_item_data;
            }
        }   

        $response_array['success'] = 1;

        return $response_array;

    }

    /**
    * @param Request $request
    * @return array
    */
    public function listMarket(Request $request)
    {
        $response = [
            'success'                   => 0,
            'error'                     => 0,
            'error_msg'                 => 0,
            'templates'                 => [],
        ];

        /** @var \App\WorkflowTemplate $template */
        $templates = WorkflowTemplate::where('archived',false)->where('published',true)->inRandomOrder()->get();
        foreach($templates as $template)
        {
            $uses_count = Workflow::where('workflow_template_uid', $template->uid)->count();

            $response['templates'][] = [
                'uid'               => $template->uid,
                'name'              => $template->name,
                'price'             => $template->price,
                'category'          => $template->category,
                'created_at_utc'    => $template->created_at_utc,
                'description'       => $template->description,
                'downloads'         => $uses_count,
                'picture_url'       => $template->picture_url,
            ];
        }

        $response['success'] = 1;

        return $response;
    }

    /**
     * Create a WorkflowTemplate from an existing Workflow
     *
     * @param Request $request
     * @param $page_uid
     * @return \App\Page|array
     * @throws \Exception
     */
    public function create(Request $request, $page_uid)
    {
        $response = [
            'success'                   => 0,
            'error'                     => 0,
            'error_msg'                 => 0,
            'template'                  => [],
        ];

        /** @var \App\User $user */
        $user = $this->user;

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        $data = $request->all();

        $workflow_uid = $data['workflow_uid'];

        /** @var \App\Workflow $workflow */
        $workflow = $page->workflows()->where('uid', $workflow_uid)->first();
        if( ! $workflow)
        {
            $response['error']      = 1;
            $response['error_msg']  = 'Workflow not found';

            return $response;
        }

        // let's validate if we have a name
        if (! isset($data['name']))
        {
            $response['error']  = 1;
            $response['error_msg'] = 'A title is needed';

            return $response;
        }

        // Now let's vaidate this name is not already taken
        $dupe_test = $user->templates()->where('name', $data['name'])->first();
        if($dupe_test)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'A template with that name already exists.';

            return $response;
        }

        // validate we have a valid number for the price
        if ( isset($data['price']))
        {
            if ( is_numeric($data['price']) )
            {
                $price = $data['price'];
            }
            else
            {
                $response['error']      = 1;
                $response['error_msg']  = 'The value set for price is not numeric';

                return $response;
            }
        }
        else
        {
            $price = null;
        }

        // validate we have a valid url for the video
        if ( isset($data['video_url']) )
        {
            if ( filter_var($data['video_url'], FILTER_VALIDATE_URL) )
            {
                $url = $data['video_url'];
            }
            else
            {
                $response['error']      = 1;
                $response['error_msg']  = 'The url set for the video is not valid';

                return $response;
            }
        }
        else
        {
            $url = null;
        }
        

        // validate we have a category and a valid category
        if (isset($data['category']))
        {
            $allowed_categories  = ['Ecommerce', 'Digital Products', 'Health & Fitness', 'Local Business', 'General'];

            if( ! in_array($data['category'], $allowed_categories, true))
            {
                // The type doesn't match, throw an error
                $response['error']      = 1;
                $response['error_msg']  = $data['category'].' is not a valid category';

                return $response;
            }
        }
        else
        {
            $response['error']      = 1;
            $response['error_msg']  = 'A category is needed.';

            return $response;
        }

        // validate we have a public field
        if( ! isset($data['public']))
        {
            // we have no public parameter
            $response['error']        = 1;
            $response['error_msg']    ='Public field is needed';

            return $response;
        }

        $response = $workflow->cloneIntoTemplate($data['name']);

        if ($response['success'])
        {
            $workflow_template = $response['template'];

            $workflow_template->name                = $data['name'];
            $workflow_template->description         = $data['description'];
            $workflow_template->video_url           = $url;
            $workflow_template->price               = $price;
            $workflow_template->category            = $data['category'];
            $workflow_template->public              = $data['public'];

            if( isset($data['picture_url']) )
            {
                // validate is a valid url
                if ( ! filter_var($data['picture_url'], FILTER_VALIDATE_URL)){
                    $response['error']              = 1;
                    $response['error_msg']          = 'The given picture_url is invalid';

                    return $response;
                }
                $workflow_template->picture_url = $data['picture_url'];
            }
            else
            {
                 // if we have a image url, then is necessary to clone too
                if ( isset($workflow->picture_url) )
                {
                    // Get the original file
                    $org_image      = file_get_contents($workflow->picture_url);
                    // Generate a new name
                    $new_filename   = 'template'.$workflow_template->uid.'_'.Str::random(22);
                    // Generate the full path
                    $full_path      = 'images/'.$new_filename;
                    // Upload the file to our storage
                    $new_file       = \Storage::disk('media')->put($full_path, $org_image, 'public');
                    // Get the URL
                    $new_file_url   = \Storage::disk('media')->url($full_path);

                    $workflow_template->picture_url = $new_file_url;
                }
            }

            $template_updated = $workflow_template->save();
        }

        unset($response['template']);

        // If the template is set to public we have to notify travis by email
        if ($data['public'] == true)
        {
            // Let's mail the template code
            $template_mail =  new TravisNewPublicTemplateNotification($workflow_template->uid, $workflow_template->user->facebook_name, $workflow_template->name);
            Mail::to('stephenson.travis@gmail.com')->send($template_mail);
        }

        return $response;
    }

    /**
     * Request that accepts a 'share_code' from a WorkflowTemplate and creates a Workflow on the provided Page
     *
     * @param Request $request
     * @param $page_uid
     * @return \App\Page|array
     * @throws \Exception
     */
    public function importWorkflow(Request $request, $page_uid)
    {
        $response = [
            'success'                   => 0,
            'error'                     => 0,
            'error_msg'                 => 0,
            'template'                  => [],
        ];

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        $share_code = $request->get('share_code');

        /** @var \App\WorkflowTemplate $template */
        $template = WorkflowTemplate::findFromShareCode($share_code);

        if( ! $template)
        {
            $response['error']      = 1;
            $response['error_msg']  = 'Template not found.';

            return $response;
        }

        if( $template->archived )
        {
            $response['error']          = 1;
            $response['error_msg']      = 'This template is not longer available';

            return $response;
        }

        // Push the template to the page
        $parameters['template_uid'] = $template->uid;
        $parameters['page_uid']     = $page->uid;

        $this->dispatch(new PushTemplateToNewWorkflow($parameters));

        $response['workflow'] = [
            'name'  => $template->name
        ];

        $response['success'] = 1;

        return $response;
    }

    /**
     * @param Request $request
     * @param $template_uid
     * @return array
     */
    public function delete(Request $request, $page_uid, $template_uid)
    {
        $response = [
            'success'                   => 0,
            'error'                     => 0,
            'error_msg'                 => 0,
            'template'                  => [],
        ];

        /** @var \App\WorkflowTemplate $template */
        $template = $this->user->templates()->where('uid', $template_uid)->first();

        if( ! $template)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Template not found with uid '.$template_uid;

            return $response;
        }

        // TODO: Actually delete the template
        $template->archived     = true;
        $template->public       = false;
        $template->published    = false;
        $template->save();

        $response['success'] = 1;

        return $response;
    }


    /**
     * Update the promomotion info for a  WorkflowTemplate 
     *
     * @param Request $request
     * @param $page_uid
     * @param $template_uid
     * @return \App\Page|array
     * @throws \Exception
     */
    public function update(Request $request, $page_uid, $template_uid)
    {
        $response = [
            'success'                   => 0,
            'error'                     => 0,
            'error_msg'                 => 0,
            'template'                  => [],
        ];

        /** @var \App\User $user */
        $user = $this->user;

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

    	$template = $this->user->templates()->where('uid', $template_uid)->first();

    	if ( ! isset($template) )
    	{
                $response['error'] = 1;
    	        $response['error_msg'] = 'The template with the uid '.$template_uid.' does not exist';

    	    return $response;
    	}

    	// Let's validate the data

        $data = $request->all();

        // \Log::debug(print_r($public,1));

        // let's validate if we have a name
        if (! isset($data['name']))
        {
            $response['error']  = 1;
            $response['error_msg'] = 'A title is needed';

            return $response;
        }

        // Now let's vaidate this name is not already taken
        $dupe_test = $user->templates()->where('name', $data['name'])->where('uid', '!=', $template_uid)->first();
        if($dupe_test)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'A template with that name already exists.';

            return $response;
        }

    	// validate we have a valid number for the price
    	if ( isset($data['price']))
    	{
            if ( is_numeric($data['price']) )
            {
                $template->price = $data['price'];
            }
            else
            {
                $response['error']      = 1;
                $response['error_msg']  = 'The value set for price is not numeric';

                return $response;
            }
    	}

    	// validate we have a valid url for the video
    	if ( isset($data['video_url']) )
    	{
            if ( filter_var($data['video_url'], FILTER_VALIDATE_URL) )
            {
                $template->video_url = $data['video_url'];    
            }
            else
            {
                $response['error']      = 1;
                $response['error_msg']  = 'The url set for the video is not valid';

                return $response;
            }
    	}
    	

        // validate we have a category and a valid category
        if (isset($data['category']))
        {
            $allowed_categories  = ['Ecommerce', 'Digital Products', 'Health & Fitness', 'Local Business', 'General'];

            if( ! in_array($data['category'], $allowed_categories, true))
            {
                // The type doesn't match, throw an error
                $response['error']      = 1;
                $response['error_msg']  = $data['category'].' is not a valid category';

                return $response;
            }
        }
        else
        {
            $response['error']      = 1;
            $response['error_msg']  = 'A category is needed.';

            return $response;
        }

        // validate we have a public field
        if( ! isset($data['public']))
        {
            // we have no public parameter
            $response['error']        = 1;
            $response['error_msg']    ='Public field is needed';

            return $response;
        }

       // vaidate is a valid url
        if (  isset($data['picture_url']) && ! filter_var($data['picture_url'], FILTER_VALIDATE_URL)){
            $response['error']              = 1;
            $response['error_msg']          = 'The given picture_url is invalid';

            return $response;
        }

        $template->name = $data['name'];
        $template->category = $data['category'];
        $template->public = $data['public'];

        // Check if we have a picture
        if( isset($data['picture_url']) )
        {
            // validate is a valid url
            if ( ! filter_var($data['picture_url'], FILTER_VALIDATE_URL)){
                $response['error']              = 1;
                $response['error_msg']          = 'The given picture_url is invalid';

                return $response;
            }
            $template->picture_url = $data['picture_url'];
        }

    	$template->save();

        // If the template is set to public we have to notify travis by email
        if ($data['public'] == true && $template->public == false)
        {

            // Let's mail the template code
            $template_mail =  new TravisNewPublicTemplateNotification($template->uid);
            Mail::to('stephenson.travis@gmail.com')->send($template_mail);
            
        }

    	$response['success'] = 1;

    	return $response;

    }

    /**
     * Buy a template from the market
     *
     * @param Request $request
     * @param $page_uid
     * @param $template_uid
     * @return Array
     * @throws \Exception
     */
    public function buy(Request $request, $page_uid, $template_uid)
    {   
        $response = [
            'success'                   => 0,
            'error'                     => 0,
            'error_msg'                 => 0,
        ];

        /** @var \App\User $user */
        $user = $this->user;

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        $template = WorkflowTemplate::find($template_uid);

        if ( ! isset($template) )
        {
                $response['error'] = 1;
                $response['error_msg'] = 'The template with the uid '.$template_uid.' does not exist';

            return $response;
        }

        // Let's chec we have the flag to attach a new source or not
        $new_source = $request->get('new_source');
        if( ! isset($new_source) )
        {
            $response['error']          = 1;
            $response['error_msg']      = 'The flag new source must be provided';

            return $response;
        }

        // Get the requested vars
        $payment_source = $request->get('src');
        if( ! isset($payment_source) )
        {
            $response['error']          = 1;
            $response['error_msg']      = 'A source must be provided';

            return $response;
        }

        try{
            // Do we already have a stripe user for this user?
            $stripe_customer_id = $user->stripe_customer_id;
            if($stripe_customer_id === null)
            {
                $new_customer = true;
                try{
                    $user->createStripeAccount($payment_source);
                    $stripe_customer_id = $user->stripe_customer_id;
                }catch(\Exception $e)
                {
                    $response['error'] = 1;
                    $response['error_msg'] = $e->getMessage();

                    \DB::rollBack();
                    return $response;
                }
            }

            // Stripe customer object
            $stripe_key = \Config::get('chatmatic.services.stripe.secret');
            \Stripe\Stripe::setApiKey($stripe_key);


            if ($new_source and ! isset($new_customer)){
                // Attache source to a customer
                \Stripe\Customer::createSource(
                    $stripe_customer_id,
                        ['source' => $payment_source]
                );
            }

            $stripe_customer_object = \Stripe\Customer::retrieve($stripe_customer_id);

            $template_price = floatval($template->price)*100;
            if ($template->price > 0.0)
            {
                try{
                    $charge  = \Stripe\Charge::create(['amount' => floatval($template->price)*100, 
                                                       'currency' => 'usd', 
                                                       'customer' => $stripe_customer_id, 
                                                       'source' => $payment_source, 
                                                       'description' => 'Purchased template  chatmatic']);
                }catch(\Exception $e)
                {
                    $response['error']              = 1;
                    $response['error_msg']          = 'Error purchasing: '.$e->getMessage();

                    \DB::rollBack();
                    return $response;
                }


                if ( ! $charge->outcome->type === 'authorized')
                {
                    $response['error']              = 1;
                    $response['error_msg']          = 'Purchase not authorized';

                    \DB::rollBack();
                    return $response;
                }
            }

            // If not price set then is zero
            if ( ! isset($template->price))
            {
                $price = 0;
            }
            else
            {
                $price = $template->price;
            }

            // Let's write the purchase on database
            $purchase                             = new \App\StripePurchase;
            $purchase->type                       = 'template';
            $purchase->total                      = $price;
            $purchase->chatmatic_buyer_uid        = $user->uid;
            $purchase->chatmatic_seller_uid       = $template->user()->first()->uid;
            $purchase->created_at_utc             = gmdate("Y-m-d\TH:i:s\Z");
            $purchase->page_uid                   = $page->uid;
            $purchase->template_uid               = $template->uid;

            $purchase->save();

            // Template code
            $template_code = \Hashids::connection('templates')->encode($template->uid);
            
            // Get billing details from customer object
            $source = $stripe_customer_object->sources->retrieve($payment_source);
            $card = $source->card;

            // Let's mail the template code
            $template_mail =  new TemplatePurchase($user->facebook_name,$template->name,$template_code);

        }catch(\Exception $e)
            {
                $response['error'] = 1;
                $response['error_msg'] = $e->getMessage();

                \DB::rollBack();
                return $response;
        }
        
        try{
            Mail::to($user->facebook_email)->send($template_mail);
        }catch(\Exception $e)
        {
            //$response['error']              = 1;
            //$response['error_msg']          = 'Templated purchased correcltly. There was an error mailing template code: '.$e->getMessage();
            //return $response;
            \Log::error(print_r('An error occurred when mailing the template code: '.$e->getMessage() ,1));
        }

        $response['success']            = 1;
        $response['template_code']      = $template_code;
        $response['billing_info']       = [
                'name'          => $template->name,
                'email'         => $user->facebook_email,
                'card_number'   => 'xxxx-xxxx-xxxx-'.$card->last4,
                'card_exp'      => $card->exp_month.'/'.$card->exp_year,
                'price'         => $template->price
            ];

        return $response;
    }

    /**
     * Show the workflow data to a preview
     *
     * @param Request $request
     * @param $page_uid
     * @param $template_uid
     * @return \App\Page|array
     * @throws \Exception
     */
    public function template_preview(Request $request, $template_uid)
    {
        $response_array = [
            'success'                   => 0,
            'error'                     => 0,
            'error_msg'                 => 0,
            'template'                  => [],
        ];

        /** @var \App\User $user */
        $user = $this->user;

        $template = WorkflowTemplate::find($template_uid);

        if ( ! isset($template) )
        {
                $response['error'] = 1;
                $response['error_msg'] = 'The template with the uid '.$template_uid.' does not exist';

            return $response;
        }

        $response_array['name']                 = $template->name;
        $response_array['description']          = $template->description;
        $response_array['price']                = $template->price;
        $response_array['category']          = $template->category;

        // Populate steps array
        foreach($template->workflowTemplateSteps()->orderBy('uid', 'asc')->get() as $steps_index => $workflow_step)
        {
            /** @var \App\WorkflowStep $workflow_step */

            $response_array['template']['steps'][$steps_index] = [
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
                $response_array['template']['steps'][$steps_index]['items'] = [];
                $response_array['template']['steps'][$steps_index]['quick_replies'] = [];
            }
            else
            {
                $response_array['template']['steps'][$steps_index]['options'] = [];
            }

            // Populate quick replies
            $quick_replies = $workflow_step->workflowTemplateStepQuickReplies()->orderBy('uid', 'asc')->get();
            foreach($quick_replies as $quick_replies_index => $quick_reply)
            {
                /** @var \App\QuickReply $quick_reply */

                $response_array['template']['steps'][$steps_index]['quick_replies'][$quick_replies_index] = [
                    'uid'               => $quick_reply->uid,
                    'reply_type'        => $quick_reply->type,
                    'reply_text'        => $quick_reply->map_text,
                    'tags'              => [],
                    'automation_uid'    => $quick_reply->automation_uid,
                    'next_step_uid'     => (int) str_replace('next-step::', '', $quick_reply->map_action_text),
                    'custom_field_uid'  => $quick_reply->custom_field_uid,
                    'custom_field_value'=> $quick_reply->custom_field_value,
                ];

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
                        'next_step_uid'     => $workflow_step_option->workflow_template_next_step_uid
                    ];

                    // Attach the array just created to the response array
                    $response_array['template']['steps'][$steps_index]['options'][$options_index] = $options_data;

                }
            } 
            else if ($workflow_step->step_type == 'delay')
            {
                $workflow_step_delay = $workflow_step->optionDelay()->first();

                $option_delay = [
                    'uid'                   => $workflow_step_delay->uid,
                    'type'                  => $workflow_step_delay->type,
                    'next_step_uid'         => $workflow_step_delay->workflow_template_next_step_uid,
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
                $response_array['template']['steps'][$steps_index]['options'] = $option_delay;
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
                                $tag = TagTemplate::where('uid',$tag_uid)->first();
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
                                $trigger = WorkflowTrigger::where('uid',$subscription_uid)->first();
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

                    $options_data = [
                        'uid'               => $workflow_step_option->uid,
                        'option'            => $workflow_step_option->name,
                        'conditions'        => $conditions,
                        'match'             => $workflow_step_option->match,
                        'next_step_uid'     => $workflow_step_option->workflow_template_next_step_uid
                    ];

                    // Attach the array just created to the response array
                    $response_array['template']['steps'][$steps_index]['options'][$options_index] = $options_data;

                }
            }
            else if ($workflow_step->step_type == 'sms')
            {
                $workflow_step_sms = $workflow_step->optionSms()->first();

                $option_sms = [
                    'uid'                   => $workflow_step_sms->uid,
                    'sms_text_message'      => $workflow_step_sms->text_message
                ];

                // Attach the array just created to the response array
                $response_array['template']['steps'][$steps_index]['options'] = $option_sms;
            }


            // Populate step items array
            foreach($workflow_step->workflowTemplateStepItems()->orderBy('item_order', 'asc')->get() as $items_index => $workflow_step_item)
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
                    foreach($workflow_step_item->workflowTemplateStepItemImages()->orderBy('uid', 'asc')->get() as $item_image_index => $workflow_step_item_image)
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
                        $image_buttons  = $workflow_step_item_image->workflowTemplateStepItemMaps()->orderBy('uid', 'asc')->get();
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
                    $free_text_button = $workflow_step_item->workflowTemplateStepItemMaps()->where('map_action', 'input')->first();

                    if($free_text_button)
                    {
                        // Determine the next_step_uid
                        $next_step = $free_text_button->map_action_text;
                        $next_step = str_replace('next-step::', '', $next_step);

                        $step_item_data['custom_field_uid'] = $free_text_button->custom_field_uid;
                        $step_item_data['next_step_uid']    = $next_step;
                        $step_item_data['automation_uid']   = $free_text_button->automation_uid;
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
                    if($step_item_image = $workflow_step_item->workflowTemplateStepItemImages()->first())
                    {
                        $step_item_data['image']                = $step_item_image->image_url;
                        $step_item_data['image_headline']       = $step_item_image->image_title;
                        $step_item_data['image_description']    = $step_item_image->image_subtitle;
                        $step_item_data['media_uid']            = $step_item_image->uid;
                        $step_item_data['image_link']           = $step_item_image->redirect_url;
                    }

                    // Populate the video
                    /** @var \App\WorkflowStepItemVideo $step_item_video */
                    if($step_item_video = $workflow_step_item->workflowTemplateStepItemVideos()->first())
                    {
                        $step_item_data['video']    = $step_item_video->video_url;
                        $step_item_data['media_uid']= $step_item_video->uid;
                    }

                    // Populate the audio
                    /** @var \App\WorkflowStepItemAudio $step_item_audio */
                    if($step_item_audio = $workflow_step_item->workflowTemplateStepItemAudios()->first())
                    {
                        $step_item_data['audio']    = $step_item_audio->audio_url;
                        $step_item_data['media_uid']= $step_item_audio->uid;
                    }

                    // So we're handling carousel's in a bit of a messy way, as such the buttons for them are actually associated with each 'pane'
                    // (in this case, represented by a workflow_step_item_image). We'll check here for anything other than a carousel and populate
                    // the buttons if so
                    $buttons            = [];
                    $step_item_buttons  = $workflow_step_item->workflowTemplateStepItemMaps()->orderBy('uid', 'asc')->where('map_action', '!=', 'input')->get();
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
                $response_array['template']['steps'][$steps_index]['items'][$items_index] = $step_item_data;
            }
        }   

        $response_array['success'] = 1;

        return $response_array;

    }

    /**
     * Buy a template from the market
     *
     * @param Request $request
     * @param $page_uid
     * @param $template_uid
     * @return Array
     * @throws \Exception
     */
    public function redem_sumo(Request $request, $page_uid, $template_uid)
    {   
        $response = [
            'success'                   => 0,
            'error'                     => 0,
            'error_msg'                 => 0,
        ];

        /** @var \App\User $user */
        $user = $this->user;

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        $template = WorkflowTemplate::find($template_uid);

        if ( ! isset($template) )
        {
                $response['error'] = 1;
                $response['error_msg'] = 'The template with the uid '.$template_uid.' does not exist';

            return $response;
        }

        // Let's check if the user has assigned an appsumo user
        $app_sumo_user = $user->sumoUser()->first();

        if ( ! $app_sumo_user)
        {
            $response['error']          = 1;
            $response['error_msg']      = 'This user has not an app sumo account';

            return $response;
        }

        // Check if this user another license to be used
        $max_template_limit = 0;

        switch ($app_sumo_user->plan_id) {
            case "chatmatic_tier1":
                $max_template_limit = 20;
                break;

            case "chatmatic_tier2":
                $max_template_limit = 40;
                break;

            case "chatmatic_tier3":
                $max_template_limit = 60;
                break;

            case "chatmatic_tier4":
                $max_template_limit = 80;
                break;

            case "chatmatic_tier5":
                $max_template_limit = 100;
                break;
        }

        if ($app_sumo_user->cloned_templates >= $max_template_limit)
        {
            $response['error']           = 1;
            $response['error_msg']       = 'This plan has no remamining templates';

            return $response;
        }

        // Write on database
        $cloned_template                        = new AppSumoClonedTemplate();
        $cloned_template->user_uid              = $user->uid;
        $cloned_template->page_uid              = $page->uid;
        $cloned_template->template_uid          = $template->uid;
        $cloned_template->created_at_utc        = Carbon::now()->toDateTimeString();

        $saved_cloned_template = $cloned_template->save();

        if ( ! $saved_cloned_template )
        {
            $response['error']          = 1;
            $response['error_msg']      = 'Transaction not written on database';

            return $response;
        }


        $template_code = \Hashids::connection('templates')->encode($template->uid);

        // Let's mail the template code
        $template_mail =  new TemplatePurchase($user->facebook_name,$template->name,$template_code);


        try{
            Mail::to($user->facebook_email)->send($template_mail);
        }catch(\Exception $e)
        {
            //$response['error']              = 1;
            //$response['error_msg']          = 'Templated purchased correcltly. There was an error mailing template code: '.$e->getMessage();
            //return $response;
            \Log::error(print_r('An error occurred when mailing the template code: '.$e->getMessage() ,1));
        }

        // Check if this is a paid template
        if ( $template->price && $template->price > 0 )
        {
            $cloned_template->paid_template = true;
            $cloned_template->save();
            // Now let's update the app sumo user record
            $app_sumo_user->cloned_templates += 1;
            $app_sumo_user->save();    
        }

        $response['success']            = 1;
        $response['template_code']      = $template_code;

        return $response;

    }
}
