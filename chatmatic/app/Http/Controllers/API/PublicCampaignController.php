<?php

namespace App\Http\Controllers\API;

use App\Campaign;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PublicCampaignController extends Controller
{

    public function show(Request $request, $public_id)
    {
        $response = [
            'success'       => 0,
            'error'         => 0,
            'error_msg'     => '',
            'campaign'      => [],
        ];

        $campaign = Campaign::where('public_id', $public_id)->first();

        if( ! $campaign)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Campaign not found';

            return $response;
        }

        $response_array = [
            'uid'               => $campaign->uid,
            'fb_id'             => $campaign->page->fb_id,
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
        ];

        $response['campaign'] = $response_array;
        $response['success'] = 1;

        return $response;
    }
}
