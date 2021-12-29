<?php

namespace App\Http\Controllers\API;

use App\AuditLog;
use App\Campaign;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CampaignController extends BaseController
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

        // Get campaigns that aren't deleted in order by newest first
        $campaigns      = $page->campaigns()->where('deleted', 0)->orderBy('uid', 'desc')->take(80)->get();
        $response_array = [];
        foreach($campaigns as $campaign)
        {
            /** @var \App\Campaign $campaign */
            $response_array[] = [
                'uid'               => $campaign->uid,
                'public_id'         => $campaign->public_id,
                'enabled'           => $campaign->enabled,
                'created_at_utc'    => Carbon::createFromTimestamp(strtotime($campaign->created_at_utc))->toDateTimeString(),
                'type'              => $campaign->type,
                'campaign_name'     => $campaign->campaign_name,
                'workflow_uid'      => $campaign->workflow_uid,
                'presubmit_title'   => $campaign->presubmit_title,
                'presubmit_body'    => $campaign->presubmit_body,
                'presubmit_image'   => $campaign->presubmit_image,
                'postsubmit_type'   => $campaign->postsubmit_type,
                'postsubmit_redirect_url'               => $campaign->postsubmit_redirect_url,
                'postsubmit_redirect_url_button_text'   => $campaign->postsubmit_redirect_url_button_text,
                'postsubmit_content_title'              => $campaign->postsubmit_content_title,
                'postsubmit_content_body'               => $campaign->postsubmit_content_body,
                'postsubmit_content_image'              => $campaign->postsubmit_content_image,
                'checkbox_plugin_button_text'           => $campaign->checkbox_plugin_button_text,
                'approval_method'   => $campaign->approval_method,
                'impressions'       => $campaign->impressions,
                'conversions'       => $campaign->conversions,
                'm_me_url'          => $campaign->m_me_url,
                'subscriptions'     => $campaign->subscriptions()->count(),
                'total_subscribers' => $campaign->subscribers()->count(),
                'today_subscribers' => $campaign->subscribers()->where('created_at_utc', Carbon::today())->count(),
                'messages_sent'     => $campaign->messagesSent()->count(),
                'messages_opened'   => $campaign->messagesSent()->where('marked_as_read', 1)->count(),
                'messages_clicked'  => $campaign->messages_clicked,
                'visits'            => $campaign->visits,
                'event_type'        => $campaign->event_type,
                'event_type_uid'    => $campaign->event_type_uid,
                'follow_up_delay'   => $campaign->follow_up_delay,
                'custom_ref'        => $campaign->custom_ref,
            ];
        }

        $elapsed    = time() - $this->start_time;
        $event_name = 'page.campaigns.loaded';
        
        AuditLog::create([
            'chatmatic_user_uid'    => $this->user->uid,
            'page_uid'              => $page->uid,
            'event'                 => $event_name,
            'message'               => count($response_array).' campaigns loaded in '.$elapsed.' seconds'
        ]);

        return $response_array;
    }

    /**
     * @param Request $request
     * @param $page_uid
     * @return array
     */
    public function create(Request $request, $page_uid)
    {
        $response = [
            'success'                   => 0,
            'error'                     => 0,
            'error_msg'                 => 0,
            'facebook_messenger_code'   => null,
            'b64_message_code'          => null,
            'public_id'                 => null,
            'uid'                       => null,
            'm_me_url'                  => null,
            'messenger_code_base_url'   => null,
        ];

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        // Validate campaign name length
        if(mb_strlen($request->get('campaign_name')) > 64)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Campaign name is too long, please limit to 64 characters or less. Current character count: '.mb_strlen($request->get('campaign_name'));

            return $response;
        }

        // Validate postsubmit_redirect_url_button_text
        if(mb_strlen($request->get('postsubmit_redirect_url_button_text')) > 64)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Postsubmit redirect url button text is too long, please limit to 64 characters or less. Current character count: '.mb_strlen($request->get('postsubmit_redirect_url_button_text'));

            return $response;
        }

        // Check for the lack of a type value (in-complete submission)
        if( ! $request->has('type'))
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Incomplete submission, please fill out all fields and complete all steps.';

            return $response;
        }

        // TODO: Validation
        $campaign = [
            'campaign_name'                         => $request->get('campaign_name'),
            'type'                                  => $request->get('type'),
            'public_id'                             => Campaign::generatePublicId(),
            'workflow_uid'                          => $request->get('workflow_uid'),
            'presubmit_title'                       => $request->get('presubmit_title')                 ?? '',
            'presubmit_body'                        => $request->get('presubmit_body')                  ?? '',
            'presubmit_image'                       => '',
            'approval_method'                       => $request->get('approval_method')                 ?? '',
            'postsubmit_type'                       => $request->get('postsubmit_type')                 ?? '',
            'postsubmit_redirect_url'               => $request->get('postsubmit_redirect_url')         ?? '',
            'postsubmit_redirect_url_button_text'   => $request->get('postsubmit_redirect_button_text') ?? '',
            'postsubmit_content_title'              => $request->get('postsubmit_content_title')        ?? '',
            'postsubmit_content_body'               => $request->get('postsubmit_content_body')         ?? '',
            'postsubmit_content_image'              => '',
            'checkbox_plugin_button_text'           => $request->get('checkbox_plugin_button_text')     ?? '',
            'custom_ref'                            => $request->get('custom_ref')                      ?? null,
            'event_type'                            => $request->get('event_type')                      ?? null,
            'event_type_uid'                        => $request->get('event_type_uid')                  ?? null,
            'follow_up_delay'                       => $request->get('follow_up_delay')                 ?? null,
        ];

        // Create the campaign
        /** @var \App\Campaign $campaign */
        $campaign               = $page->campaigns()->create($campaign);

        $campaign->presubmit_image          = $request->get('presubmit_image')                 ?? '';
        $campaign->postsubmit_content_image = $request->get('postsubmit_content_image')        ?? '';

        // Are there images we need to handle?
        // If there's a value in the image field and it doesn't contain http (a url) then it's hopefully base64 meaning a new file
        if(mb_stristr($campaign->presubmit_image, 'data:image'))
        {
            $campaign_image_url = $campaign->uploadImage($campaign->presubmit_image);
            if($campaign_image_url['error'])
                return $campaign_image_url;
            $campaign_image_url = $campaign_image_url['url'];
            $campaign->presubmit_image = $campaign_image_url;
        }

        if(mb_stristr($campaign->postsubmit_content_image, 'data:image'))
        {
            $campaign_image_url = $campaign->uploadImage($campaign->postsubmit_content_image);
            if($campaign_image_url['error'])
                return $campaign_image_url;
            $campaign_image_url = $campaign_image_url['url'];
            $campaign->postsubmit_content_image = $campaign_image_url;
        }

        $campaign->save();

        $response['uid']        = $campaign->uid;
        $response['public_id']  = $campaign->public_id;

        // Generate the m.me url
        if($campaign->type === 'm_dot_me')
        {
            // Setup ref string
            if($campaign->custom_ref === null || $campaign->custom_ref === '' || ! isset($campaign->custom_ref))
            {
                $ref                    = 'campaign::' . $campaign->public_id;
                $m_me_url               = $campaign->generateMDotMeURL($ref);
            }
            else
            {
                // Confirm this custom_ref isn't already in use by another campaign on this page
                $dupe_check = $page->campaigns()->where('custom_ref', $campaign->custom_ref)->first();
                if($dupe_check)
                {
                    $response_array['error'] = 1;
                    $response_array['error_msg'] = 'This custom ref is already is in use on another page.';

                    return $response_array;
                }

                $ref                    = $campaign->custom_ref;
                $m_me_url               = $campaign->generateMDotMeURL($ref);
            }
            $campaign->m_me_url     = $m_me_url;

            // Setup response data
            $response['m_me_url']   = $m_me_url;
        }

        // Update these newly created values
        $campaign->save();

        if ($campaign->type === 'scan_refurl')
        {
            // Get Messenger Code
            $facebook_message_code      = null;
            $messenger_code_response    = $campaign->generateScanCode();

            if($messenger_code_response['error'] === 1)
                return $messenger_code_response;

            // Setup response data
            $response['facebook_messenger_code']  = $messenger_code_response['facebook_messenger_code'];
            $response['b64_message_code']         = $messenger_code_response['b64_message_code'];
        }

        $response['success']    = 1;

        return $response;
    }

    /**
     * @param Request $request
     * @param $page_uid
     * @param $campaign_uid
     * @return array
     */
    public function delete(Request $request, $page_uid, $campaign_uid)
    {
        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        // First confirm the campaign exists, otherwise throw exception
        $campaign = $page->campaigns()->where('uid', $campaign_uid)->firstOrFail();

        // Delete the campaign
        $campaign->deleted = 1;
        $campaign->save();

        return ['success' => true];
    }

    /**
     * @param Request $request
     * @param $page_uid
     * @param $campaign_uid
     * @return array
     */
    public function update(Request $request, $page_uid, $campaign_uid)
    {
        // Build response array
        $response_array = [
            'success'   => 0,
            'error'     => 0,
            'error_msg' => ''
        ];

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        /** @var \App\Campaign $campaign */

        // First confirm the campaign exists, otherwise throw exception
        $campaign = $page->campaigns()->where('uid', $campaign_uid)->firstOrFail();

        // TODO: Validation on all of this
        $campaign->campaign_name                        = $request->get('campaign_name');
        $campaign->presubmit_title                      = $request->get('presubmit_title')                      ?? '';
        $campaign->presubmit_body                       = $request->get('presubmit_body')                       ?? '';
        $campaign->presubmit_image                      = $request->get('presubmit_image')                      ?? '';
        $campaign->approval_method                      = $request->get('approval_method')                      ?? '';
        $campaign->postsubmit_type                      = $request->get('postsubmit_type')                      ?? '';
        $campaign->postsubmit_redirect_url              = $request->get('postsubmit_redirect_url')              ?? '';
        $campaign->postsubmit_redirect_url_button_text  = $request->get('postsubmit_redirect_url_button_text')  ?? '';
        $campaign->postsubmit_content_title             = $request->get('postsubmit_content_title')             ?? '';
        $campaign->postsubmit_content_body              = $request->get('postsubmit_content_body')              ?? '';
        $campaign->postsubmit_content_image             = $request->get('postsubmit_content_image')             ?? '';
        $campaign->checkbox_plugin_button_text          = $request->get('checkbox_plugin_button_text')          ?? '';
        $campaign->workflow_uid                         = $request->get('workflow_uid');

        if($request->has('custom_ref'))
            $campaign->custom_ref                       = $request->get('custom_ref');
        else
            $campaign->custom_ref                       = null;

        if($request->has('event_type'))
            $campaign->event_type                       = $request->get('event_type');
        else
            $campaign->event_type                       = null;

        if($request->has('event_type_uid'))
            $campaign->event_type_uid                   = $request->get('event_type_uid');
        else
            $campaign->event_type_uid                   = null;

        if($request->has('follow_up_delay'))
            $campaign->follow_up_delay                  = $request->get('follow_up_delay');
        else
            $campaign->follow_up_delay                  = null;


        // Are there images we need to handle?
        // If there's a value in the image field and it doesn't contain http (a url) then it's hopefully base64 meaning a new file
        if(mb_stristr($campaign->presubmit_image, 'data:image'))
        {
            $campaign_image_url = $campaign->uploadImage($campaign->presubmit_image);
            if($campaign_image_url['error'])
                return $campaign_image_url;
            $campaign_image_url = $campaign_image_url['url'];
            $campaign->presubmit_image = $campaign_image_url;
        }

        if(mb_stristr($campaign->postsubmit_content_image, 'data:image'))
        {
            $campaign_image_url = $campaign->uploadImage($campaign->postsubmit_content_image);
            if($campaign_image_url['error'])
                return $campaign_image_url;
            $campaign_image_url = $campaign_image_url['url'];
            $campaign->postsubmit_content_image = $campaign_image_url;
        }
        
        // If there's a custom ref we need to update the m_me_url
        if($campaign->custom_ref !== null)
        {
            // Confirm this custom_ref isn't already in use by another campaign on this page
            $dupe_check = $page->campaigns()->where('custom_ref', $campaign->custom_ref)->first();
            if($dupe_check && $dupe_check->uid !== $campaign->uid)
            {
                $response_array['error'] = 1;
                $response_array['error_msg'] = 'This custom ref is already is in use on another page.';

                return $response_array;
            }

            $ref                    = $campaign->custom_ref;
            $campaign->m_me_url     = $campaign->generateMDotMeURL($ref);
        }

        try{
            $campaign->save();
        }catch (\Exception $e)
        {
            $response_array['error']        = 1;
            $response_array['error_msg']    = $e->getMessage();
        }

        if( ! $response_array['error'])
            $response_array['success'] = 1;

        return $response_array;
    }
}
