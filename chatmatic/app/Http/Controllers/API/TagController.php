<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Taggable;
use App\Subscriber;

class TagController extends BaseController
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

        $response = [];
        foreach($page->tags()->where('archived', false)->get() as $tag)
        {
            $response[] = [
                'uid'       => $tag->uid,
                'value'     => $tag->value,
                'count'     => Taggable::where('tag_uid',$tag->uid)->where('taggable_type','App\Subscriber')->count() // subscribers
                //'archived'  => $tag->archived
            ];
        }

        return $response;
    }

    /**
     * @param Request $request
     * @param $page_uid
     * @return array
     */
    public function create(Request $request, $page_uid)
    {
        $response = [
            'success'   => 0,
            'error'     => 0,
            'error_msg' => '',
            'uid'       => null,
        ];

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        if( ! $request->has('value'))
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Required value "value" not supplied.';

            return $response;
        }

        // TODO: Validate the tags value
        $tag_label  = $request->get('value');

        // Confirm this page doesn't already have this tag
        $tag = $page->tags()->where('value', $tag_label)->first();
        if( ! $tag)
        {
            $tag = $page->tags()->create([
                'value'     => $tag_label,
                'keyword'   => ''
            ]);
        }

        $response['success'] = 1;
        $response['uid'] = $tag->uid;

        return $response;
    }

    /**
     * @param Request $request
     * @param $page_uid
     * @return array
     */
    public function delete(Request $request, $page_uid, $tag_uid)
    {
        $response = [
            'success'   => 0,
            'error'     => 0,
            'error_msg' => '',
        ];

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        $tag = $page->tags()->where('uid', $tag_uid)->first();
        if( ! $tag)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Tag not found.';

            return $response;
        }

        $tag->archived = 1;
        $tag->save();

        $response['success'] = 1;

        return $response;
    }


    /**
     * @param Request $request
     * @param $page_uid
     * @return array
     */
    public function subscribers(Request $request, $page_uid, $tag_uid)
    {
        $response = [
            'success'       => 0,
            'error'         => 0,
            'error_msg'     => '',
            'subscribers'   => [],
        ];

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        $tag = $page->tags()->where('uid', $tag_uid)->first();
        if( ! $tag)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Tag not found.';

            return $response;
        }

        $subuscribers_tagged = Taggable::where('tag_uid',$tag_uid)->where('taggable_type','App\Subscriber')->get();

        foreach ($subuscribers_tagged as $subscriber_tagged) 
        {
            $subscriber = Subscriber::find($subscriber_tagged->taggable_uid);

            $response['subscribers'][] = [
                        'names'         => $subscriber->first_name.' '.$subscriber->last_name,
                        'profile_pic'   => $subscriber->profile_pic_url,
                    ];
        }
        

        $response['success'] = 1;

        return $response;
    }
}
