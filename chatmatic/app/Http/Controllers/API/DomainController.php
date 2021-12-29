<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;

// This controller is used to add/update/remove WHITELISTED_DOMAIN
// https://developers.facebook.com/docs/messenger-platform/reference/messenger-profile-api/domain-whitelisting

class DomainController extends BaseController
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

        $domains = $page->retrieveWhiteList();

        if($domains['error'] === 1)
        {
        	return [
        		'error'			=> 1,
        		'error_msg' 	=> $domains['error_msg']

        	];
        }

        return [
        	'success'		=> 1,
        	'urls'			=> $domains['urls']
        ];
    }

    public function update(Request $request, $page_uid){
    	$page = $this->getPage($page_uid);
    	if($page['error'] === 1)
    	{
    		return $page;
    	}
    	/** @var \App\Page $page */
    	$page = $page['page'];

    	// Dara from request
    	$urls = $request->get('urls');

        $response = $page->updateWhiteList($urls);

        if($response['error'] ==- 1)
        {
            return [
                'error'         => 1,
                'error_msg'     => $domains['error_msg']

            ];   
        }

        return [
            'success'       => 1
        ];


    }

}
