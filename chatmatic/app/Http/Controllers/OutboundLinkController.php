<?php

namespace App\Http\Controllers;

use App\WorkflowTrigger;
use App\OutboundLink;
use App\Subscriber;
use App\Taggable;
use App\WorkflowStepItemMap;
use App\SubscriberClickHistory;
use Illuminate\Http\Request;

class OutboundLinkController extends Controller
{

    /**
     * @param Request $request
     * @param $link_slug
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function show(Request $request, $link_slug, $trigger_uid, $button_uid, $subscriber_uid)
    {
        // For some strange reason we are getting "global.js.php" as a subscriber_uid
        $subscriber_validator = (int)$subscriber_uid;
        if( $subscriber_uid != 0 & $subscriber_validator == 0 )
        {
            return null;
        }

        $link           = OutboundLink::whereSlug($link_slug)->with('workflow')->firstOrFail();
        $redirect_url   = $link->url;
        $trigger        = WorkflowTrigger::find($trigger_uid); 
        $button         = workflowStepItemMap::find($button_uid);
        $subscriber = Subscriber::find($subscriber_uid);

        if(isset($subscriber))
        {
            $subscriber->increment('total_clicks');

            // Let's tag the user if we have a tag assignated
            $button_tag = Taggable::where('taggable_type', '=' ,'App\WorkflowStepItemMap')->where('taggable_uid','=',$button->uid)->first();
            

            if( isset($button_tag) )
            {   
                // Let's check he's not already subscribed
                $already = Taggable::where('taggable_type', '=' ,'App\Subscriber')->where('taggable_uid','=',$subscriber_uid)->where('tag_uid',$button_tag->tag_uid)->first();

                if ( ! isset($already))
                {
                    $subscriber_tag                     = new Taggable;
                    $subscriber_tag->taggable_uid       = $subscriber->uid;
                    $subscriber_tag->taggable_type      = 'App\Subscriber';
                    $subscriber_tag->tag_uid            = $button_tag->tag_uid;

                    $subscriber_tag->save();
                }
            }
        }

        // Increment the 'clicks' value on the button (If the button still exists in the database, otherwise ignore the increment)
        if(isset($button))
        {
            $button->increment('clicks');
        }
        // Increment the 'clicks' value on the image (If the image still exists in the database, otherwise ignore the increment)
        if($link->workflow_step_item_image_uid !== null && $link->workflowStepItemImage !== null)
        {
            $link->workflowStepItemImage->increment('clicks');
        }

        $link->workflowStep->increment('messages_clicked');


        // Increment the 'messages_clicked' value on the workflow record
        // $link->workflow->increment('messages_clicked');

        // Increment the 'redirect_count' on the link record
        $link->increment('redirect_count');

        // Increment the workflow trigger counter
        $trigger->increment('messages_clicked');

        if(isset($subscriber) && $subscriber)
        {
            // Determine if there are any merge fields in use on this URL at all
            if(mb_stristr($redirect_url, '{'))
            {
                // There's at least one merge field in use - let's populate our array of potential replacements
                // We HAVE to have a subscriber by this point - TODO: Confirm with Fabian that subscriber is being sent over

                // First we'll populate the simple merge fields
                $merge_fields = [
                    '{fname}' => $subscriber->first_name,
                    '{lname}' => $subscriber->last_name,
                    '{email}' => $subscriber->email,
                    '{phone}' => $subscriber->phone_number,
                ];

                // Next we'll populate based on any custom field responses that exist for this subscriber
                foreach($subscriber->customFieldResponses as $customFieldResponse)
                {
                    $value      = $customFieldResponse->response;
                    $merge_tag  = $customFieldResponse->customField->merge_tag;

                    $merge_fields[$merge_tag] = $value;
                }

                // Now let's parse the redirect url and replace the merge tags with their urlencoded representative values
                foreach($merge_fields as $find => $replace)
                {
                    $redirect_url = str_ireplace($find, urlencode($replace), $redirect_url);
                }
            }
        }

        // Write on susbcrberclickhsitory table
        if (isset($subscriber))
        {

            $step = $button->workflowStep;
            $element_clicked = SubscriberClickHistory::where('subscriber_uid',$subscriber->uid)
                                                        ->where('workflow_step_uid',$step->uid)
                                                        ->where('click_type','button')
                                                        ->first();

            if (isset($element_clicked))
            {
                // if already have clicked on any button of this step
                if ($element_clicked->element_uid != $button_uid )
                {
                    $element_clicked->element_uid = $button_uid;
                    $element_clicked->save();

                }
            }
            else
            {
                $new_click = new SubscriberClickHistory;
                $new_click->subscriber_uid      = $subscriber->uid;
                $new_click->workflow_step_uid   = $step->uid;
                $new_click->workflow_uid        = $step->workflow_uid;
                $new_click->click_type          = 'button';
                $new_click->element_uid         = $button_uid;
                $new_click->element_uid         = $button_uid;
                                        
                $new_click->save();
            }
        }

        // Remove start/end forward slashes
        // This is in place because Travis is reporting that when using merge tags the redirect-to URL starts with a forward slash
        $redirect_url = trim($redirect_url, '/');

        // Reconstruct the URL in the case that it was a full URL as the merge tag and we've urlencoded() it
        $redirect_url = str_ireplace('%3A%2F%2F', '://', $redirect_url); // Replace ://
        $redirect_url = str_ireplace('%2F', '/', $redirect_url); // Replace forward slashes

        $redirect_url = urldecode($redirect_url);

        return redirect($redirect_url);
    }
}
