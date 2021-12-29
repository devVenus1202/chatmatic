<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Log;

/**
 * App\WorkflowStepItem
 *
 * @property int $uid
 * @property int $workflow_step_uid
 * @property string $item_type
 * @property string $headline
 * @property string $content
 * @property int $workflow_uid
 * @property int $page_uid
 * @property string $text_message
 * @property-read \App\Page $page
 * @property-read \App\Workflow $workflow
 * @property-read \App\WorkflowStep $workflowStep
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\WorkflowStepItemImage[] $workflowStepItemImages
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\WorkflowStepItemMap[] $workflowStepItemMaps
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItem whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItem whereHeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItem whereItemType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItem wherePageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItem whereTextMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItem whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItem whereWorkflowStepUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItem whereWorkflowUid($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItem query()
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\WorkflowStepItemVideo[] $workflowStepItemVideos
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\WorkflowStepItemAudio[] $workflowStepItemAudios
 * @property int|null $order
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItem whereOrder($value)
 * @property int|null $item_order
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItem whereItemOrder($value)
 * @property-read int|null $workflow_step_item_audios_count
 * @property-read int|null $workflow_step_item_images_count
 * @property-read int|null $workflow_step_item_maps_count
 * @property-read int|null $workflow_step_item_videos_count
 */
class WorkflowStepItem extends Model
{
    protected $table        = 'workflow_step_items';
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workflowStepItemMaps()
    {
        return $this->hasMany(WorkflowStepItemMap::class, 'workflow_step_item_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workflowStepItemImages()
    {
        return $this->hasMany(WorkflowStepItemImage::class, 'workflow_step_item_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workflowStepItemVideos()
    {
        return $this->hasMany(WorkflowStepItemVideo::class, 'workflow_step_item_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workflowStepItemAudios()
    {
        return $this->hasMany(WorkflowStepItemAudio::class, 'workflow_step_item_uid', 'uid');
    }

    /**
     * Find/Replace the merge fields in the step item's text_message field
     *
     * @param $subscriber_uid
     */
    public function processMergeFields($subscriber_uid)
    {
        // Grab the subscriber we're sending this message to
        $subscriber = $this->page->subscribers()->where('user_psid', $subscriber_uid)->first();

        // Set the merge fields that are available
        $merge_fields = [
            '{fname}' => $subscriber->first_name,
            '{lname}' => $subscriber->last_name
        ];

        // Loop through the merge fields
        $text_message = $this->text_message;
        foreach($merge_fields as $merge_field => $replace_text)
        {
            // Replace merge field with new value
            $text_message = str_replace($merge_field, $replace_text, $text_message);
        }

        // Replace the 'text_message' field with our new updated text_message
        $this->text_message = $text_message;
    }

    /**
     * @return array
     */
    public function buildButtonsArray()
    {
        $buttons = [];
        if($this->item_type === 'text')
        {
            if($this->workflowStepItemMaps()->count())
            {
                // Set the buttons
                foreach($this->workflowStepItemMaps()->orderBy('uid', 'asc')->get() as $workflow_step_item_map)
                {
                    $buttons[] =
                        [
                            'type'          => $workflow_step_item_map->map_action,
                            'button_text'   => str_limit($workflow_step_item_map->map_text, 20),
                            'button_action' => $workflow_step_item_map->map_action_text
                        ];
                }
            }
        }
        elseif($this->item_type === 'card' || $this->item_type === 'carousel')
        {
            if($this->workflowStepItemMaps()->count())
            {
                // Set the buttons
                foreach($this->workflowStepItemMaps()->orderBy('uid', 'asc')->get() as $workflow_step_item_map)
                {
                    $button = [
                        'type'    => $workflow_step_item_map->map_action,
                        'title'   => str_limit($workflow_step_item_map->map_text, 20),
                    ];

                    switch($workflow_step_item_map->map_action)
                    {
                        case 'web_url':
                            $button['url'] = $workflow_step_item_map->map_action_text;
                            break;

                        case 'postback':
                            $button['payload'] = $workflow_step_item_map->map_action_text;
                            break;
                    }

                    $buttons[] = $button;
                }
            }
        }

        return $buttons;
    }

    /**
     * We're going to delete this workflow step item, we want to also delete any children elements
     *  - images
     *  - buttons
     *  - audio
     *  - video
     *
     * @throws \Exception
     */
    public function deleteWithChildren()
    {
        $this->workflowStepItemImages()->delete();
        $this->workflowStepItemMaps()->delete();
        $this->workflowStepItemAudios()->delete();
        $this->workflowStepItemVideos()->delete();

        $this->delete();
    }

    /**
     * Prepare this Workflow record object to be templated by removing unneeded values and setting
     * appropriate template values
     *
     * @param WorkflowStepItem $workflowStepItem
     * @param WorkflowTemplate $workflowTemplate
     * @param WorkflowTemplateStep $workflowTemplateStep
     * @return array
     */
    public static function prepareForTemplate(WorkflowStepItem $workflowStepItem, WorkflowTemplate $workflowTemplate, WorkflowTemplateStep $workflowTemplateStep)
    {
        $workflowTemplateStepItem = $workflowStepItem->toArray();

        // Unset the stuff we don't need/will replace
        unset($workflowTemplateStepItem['uid']);
        unset($workflowTemplateStepItem['workflow_step_uid']);
        unset($workflowTemplateStepItem['workflow_uid']);
        unset($workflowTemplateStepItem['page_uid']);

        // Set the stuff we need
        $workflowTemplateStepItem['workflow_template_step_uid'] = $workflowTemplateStep->uid;
        $workflowTemplateStepItem['workflow_template_uid']      = $workflowTemplate->uid;

        // Return the array
        return $workflowTemplateStepItem;
    }

    /**
     * Generate a random string (used for filenames)
     *
     * @param $length
     * @return string
     */
    public static function generateRandomString($length)
    {
        $characters         = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength   = strlen($characters);
        $randomString       = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * @param $request_step_item_data
     * @param $workflow_step
     * @param $temp_step_uid_map
     * @return mixed
     * @throws \Exception
     */
    public static function updateOrCreate($request_step_item_data, $workflow_step, $temp_step_uid_map, $temp_button_uid_map)
    {
        /** @var \App\Workflow $workflow */
        /** @var \App\Page $page */
        /** @var \App\WorkflowStep $workflow_step */
        /** @var \App\WorkflowStepItem $workflow_step_item */

        $response['success']                = 0;
        $response['error']                  = 0;
        $response['error_msg']              = '';
        $response['workflow_step_item']     = null;
        $response['temp_button_uid_map']    = null;

        $workflow   = $workflow_step->workflow;
        $page       = $workflow->page;
        $item       = $request_step_item_data;

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
        if( ! in_array($item['type'], $step_types_allowed, true))
        {
            // workflow step item type doesn't match allowed types
            $response['error']      = 1;
            $response['error_msg']  = 'Workflow step item type mismatch ('.$item['type'].')';

            return $response;
        }

        // If there's a uid then it's an existing step
        if(isset($item['uid']))
        {
            $workflow_step_item = $workflow_step->workflowStepItems()->where('uid', $item['uid'])->first();

            // Throw an error if we didn't get a step item
            if( ! $workflow_step_item)
            {
                // workflow step item not found
                $response['error']      = 1;
                $response['error_msg']  = 'Workflow step item not found ('.$item['uid'].')';

                return $response;
            }

            // Update the type
            $workflow_step_item->item_type = $item['type'];

            // Update the text/headline/content fields
            if($workflow_step_item->item_type === 'text' || $workflow_step_item->item_type === 'free_text_input')
            {
                $workflow_step_item->text_message = $item['text_message'];
            }
            else
            {
                $workflow_step_item->headline   = $item['headline'] ?? '';
                $workflow_step_item->content    = $item['description'] ?? '';
            }
        }
        else // It's a new step item
        {
            // Build the step item
            $workflow_step_item = new self;
            $workflow_step_item->workflow_uid       = $workflow->uid;
            $workflow_step_item->workflow_step_uid  = $workflow_step->uid;
            $workflow_step_item->page_uid           = $page->uid;
            $workflow_step_item->item_type          = $item['type'];

            // Set these to empty strings for now, will populate later
            $workflow_step_item->headline           = '';
            $workflow_step_item->content            = '';
            $workflow_step_item->text_message       = '';

            // The 'text' type messages store their payload/text in the 'text_message' column
            if($workflow_step_item->item_type === 'text')
                $workflow_step_item->text_message       = $item['text_message'] ?? '';
            else
            {
                $workflow_step_item->headline           = $item['headline'] ?? '';
                $workflow_step_item->content            = $item['description'] ?? '';
            }
        }

        // Set the order
        $workflow_step_item->item_order = $item['order'];

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

                // Loop through to determine any existing carousel items that don't exist in our request items to flag for deletion
                $existing_carousel_items = $workflow_step_item->workflowStepItemImages()->orderBy('uid', 'asc')->get();
                foreach($existing_carousel_items as $existing_carousel_item)
                {
                    // If this $existing_carousel_item doesn't have a representation in the request items we can delete it
                    $exists = false;
                    foreach($carousel_items as $carousel_item)
                    {
                        if($carousel_item['media_uid'] === $existing_carousel_item->uid)
                        {
                            $exists = true;
                        }
                    }

                    if( ! $exists)
                    {
                        // Delete the buttons associated with this carousel pane/image
                        // $existing_carousel_item->workflowStepItemMaps()->delete();
                        // Delete the carousel pane/image
                        $existing_carousel_item->delete();
                    }
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

                        return $response;
                    }

                    // Process the buttons associated with this image/carousel step
                    $carousel_item_buttons = $carousel_item['action_btns'];

                    // Loop through and find the deleted buttons
                    $existing_buttons = $carousel_image->workflowStepItemMaps()->get();
                    foreach($existing_buttons as $existing_button)
                    {
                        $exists = false;
                        foreach($carousel_item_buttons as $carousel_item_button)
                        {
                            if(isset($carousel_item_button['uid']) && $carousel_item_button['uid'] === $existing_button->uid)
                            {
                                $exists = true;
                            }
                        }

                        if( ! $exists)
                        {
                            $existing_button->delete();
                        }
                    }

                    if(count($carousel_item_buttons))
                    {
                        // Loop through provided buttons creating them
                        foreach($carousel_item_buttons as $carousel_item_button)
                        {
                            // If there's no actual data (this is a UI bug, this code can be removed when Github issue: https://github.com/mferrara/chatmatic_react/issues/94 is resolved)
                            if(isset($carousel_item_button['label']) && ! isset($carousel_item_button['action_type']))
                            {
                                continue; // Skip the rest of this loop for this iteration and move to the next
                            }

                            // If there is a uid value it's an existing button and we'll try to edit that button otherwise we'll create a new one
                            if(isset($carousel_item_button['uid']) && is_integer($carousel_item_button['uid']) )
                            {
                                $button = WorkflowStepItemMap::find($carousel_item_button['uid']);

                                // If we don't have a button we'll throw an error
                                if( ! $button)
                                {
                                    // Error finding button
                                    $response['error']      = 1;
                                    $response['error_msg']  = 'Error saving carousel button with label of: '.$carousel_item_button['label'].' because it is no longer found on the existing Engagement.';

                                    return $response;
                                }
                            }
                            else
                            {
                                $button = new WorkflowStepItemMap;
                            }

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
                                    if( ! isset($carousel_item_button['next_step_uid']))
                                    {
                                        // Workflow step this button is referencing can't be found
                                        $response['error']      = 1;
                                        $response['error_msg']  = 'Workflow step referenced not found: 0x06 '.$carousel_item_button['next_step_uid'].'.';

                                        return $response;
                                    }

                                    // Obtain the uid of the next step by checking the $temp_step_uid_map array
                                    $next_step = $carousel_item_button['next_step_uid'];
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

                                return $response;
                            }

                            // Associate tags with the button
                            $button_tags = $carousel_item_button['tags'];
                            if(count($button_tags))
                            {
                                $attach_tags = [];
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

                                        return $response;
                                    }

                                    if( ! in_array($tag->uid, $attach_tags, true))
                                        $attach_tags[] = $tag->uid;
                                }

                                // Empty existing tags
                                $button->tags()->sync([]);
                                // Associate the tags
                                $button->tags()->sync($attach_tags);
                            }
                            else
                            {
                                // Associate the tags
                                $button->tags()->sync([]);
                            }
                        }
                    }
                }

                break;

            case 'text':
                // We don't need to do anything here
                break;

            case 'delay':
                // Format the delay/typing indicator parameters into the 'content' column as json ("{'typing':true,'delay':3}")
                $delay_payload = [
                    'typing'    => $item['show_typing'],
                    'delay'     => $item['delay_time'],
                ];
                $workflow_step_item->content = json_encode($delay_payload);
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

                $map_action_text = 'next-step::'.$next_step;

                // Does the button already exist? It would be the only 'input' type button with this step_uid
                $free_text_button = WorkflowStepItemMap::where('workflow_step_item_uid', $workflow_step_item->uid)->where('map_action', '=', 'input')->first();

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

                // Button already exists, let's make sure it's pointing to the right place
                if($free_text_button)
                {
                    $free_text_button->custom_field_uid = $item['custom_field_uid'];
                    $free_text_button->map_action_text  = $map_action_text;
                    $free_text_button->automation_uid   = $item['automation_uid'];
                    $free_text_button->save();
                }
                else
                {
                    // Doesn't exist, we'll create it
                    $free_text_button = new WorkflowStepItemMap;
                    $free_text_button->map_action           = 'input';
                    $free_text_button->map_action_text      = $map_action_text;
                    $free_text_button->map_text             = ''; // Can't be null
                    $free_text_button->page_uid             = $page->uid;
                    $free_text_button->workflow_uid         = $workflow->uid;
                    $free_text_button->workflow_step_uid    = $workflow_step->uid;
                    $free_text_button->workflow_step_item_uid = $workflow_step_item->uid;
                    $free_text_button->custom_field_uid     = $item['custom_field_uid'];
                    $free_text_button->automation_uid       = $item['automation_uid'];
                    $free_text_button->save();
                }

                // Associate tags with the button
                if(isset($item['tags']))
                {
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
                }

                break;
        }

        // Process buttons
        // As long as it's not a carousel we'll process the buttons
        if($workflow_step_item->item_type !== 'carousel')
        {
            $request_buttons = [];
            if(isset($item['action_btns']))
            {
                $request_buttons = $item['action_btns'];
            }

            // Loop through existing buttons to determine if they are still represented in the request data, if not, they've been removed so we'll delete them
            // Loop through and find the deleted buttons
            $existing_buttons = $workflow_step_item->workflowStepItemMaps()->get();
            foreach($existing_buttons as $existing_button)
            {
                // If it's an 'input' type button we'll skip it since this button type won't be find in the $request_buttons array
                if($existing_button->map_action === 'input')
                {
                    continue;
                }

                $exists = false;
                foreach($request_buttons as $request_button)
                {
                    if(isset($request_button['uid']) && $request_button['uid'] === $existing_button->uid)
                    {
                        $exists = true;
                    }
                }

                if( ! $exists)
                {
                    $existing_button->delete();
                }
            }

            // Process the buttons associated with this step
            if(isset($item['action_btns']))
            {
                $step_item_buttons = $item['action_btns'];

                // Loop through provided buttons creating them
                foreach($step_item_buttons as $step_item_button)
                {
                    if(isset($step_item_button['uid']) && is_numeric($step_item_button['uid']))
                    {
                        $button = $workflow_step_item->workflowStepItemMaps()->where('uid', $step_item_button['uid'])->first();
                    }
                    else
                    {
                        $button = new WorkflowStepItemMap;
                        $button->page_uid                       = $page->uid;
                        $button->workflow_uid                   = $workflow->uid;
                        $button->workflow_step_uid              = $workflow_step->uid;
                        $button->workflow_step_item_uid         = $workflow_step_item->uid;
                    }

                    $button->map_text                       = $step_item_button['label'];
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
                                $next_step = WorkflowStep::where('page_uid', $page->uid)->where('uid', $step_item_button['next_step_uid'])->first();
                                if($next_step === null)
                                {
                                    // Workflow step this button is referencing can't be found
                                    $response['error']      = 1;
                                    $response['error_msg']  = 'Workflow step referenced not found: 0x05 '.$step_item_button['next_step_uid'].'.';

                                    // Rollback our database changes
                                    \DB::rollBack();

                                    return $response;
                                }
                                $next_step = $next_step->uid;
                            }

                            $button->map_action_text = 'next-step::'.$next_step;
                            break;

                        case 'web_url':

                            if(mb_strlen($step_item_button['open_url']) > 255)
                            {
                                $response['error']      = 1;
                                $response['error_msg']  = 'URL: '.$step_item_button['open_url'].' is too long, maximum length is 255 characters.';

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

                        return $response;
                    }

                    // Associate tags with the button
                    $button_tags = $step_item_button['tags'];
                    if(count($button_tags))
                    {
                        $sync_tags = [];
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

                                return $response;
                            }

                            $sync_tags[] = $tag->uid;
                        }

                        // Associate the tag
                        $button->tags()->sync([]);
                        $button->tags()->sync($sync_tags);
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

            return $response;
        }

        $response['success'] = 1;
        $response['workflow_step_item'] = $workflow_step_item;
        $response['temp_button_uid_map'] = $temp_button_uid_map;

        return $response;
    }
}
