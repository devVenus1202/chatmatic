<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Page;
use Illuminate\Http\Request;

class IntegrationTriggerController extends Controller
{
    /**
     * @param Request $request
     * @param $page_uid
     * @param $integration_uid
     * @return \App\IntegrationRecord|array|mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function trigger(Request $request, $page_uid, $integration_uid)
    {
        $response = [
            'success'               => 0,
            'error'                 => 0,
            'error_msg'             => 0,
            'integration_record'    => []
        ];

        /** @var \App\Page $page */
        $page = Page::where('uid', $page_uid)->firstOrFail();

        /** @var \App\Integration $integration */
        $integration = $page->integrations()->where('uid', $integration_uid)->first();

        if( ! $integration)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Integration not found';

            return $response;
        }

        $subscriber_psid = $request->get('subscriber_psid');

        // Send the integration
        $integration_record = $integration->send($subscriber_psid);

        if($integration_record['success'] !== 1)
        {
            return $integration_record;
        }

        /** @var \App\IntegrationRecord $integration_record */
        $integration_record = $integration_record['integration_record'];

        $response['success']            = 1;
        $response['integration_record'] = [
            'uid'                       => $integration_record->uid,
            'integration_uid'           => $integration_record->integration_uid,
            'integration_type_uid'      => $integration_record->integration_type_uid,
            'page_uid'                  => $integration_record->page_uid,
            'success'                   => $integration_record->success,
            'payload'                   => $integration_record->payload,
            'response'                  => $integration_record->response,
        ];

        return $response;
    }
}
