<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use Illuminate\Http\Request;

class AutomationController extends BaseController
{
    /**
     * @param Request $request
     * @param $page_uid
     * @return array
     */
    public function index(Request $request, $page_uid)
    {
        $response = [
            'success'       => 0,
            'error'         => 0,
            'error_msg'     => '',
            'automations'   => [],
        ];
        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        $automations = $page->automations()->orderBy('uid', 'asc')->get();

        /** @var \App\Automation $auto */
        foreach($automations as $auto)
        {
            $tags = $auto->tags()->get();

            $auto_array = [
                'uid'                   => $auto->uid,
                'page_uid'              => $auto->page_uid,
                'active'                => $auto->active,
                'within_day'            => $auto->executions()->whereDate('created_at_utc', Carbon::now('UTC')->subDay())->count(),
                'total'                 => $auto->executions()->count(),
                'name'                  => $auto->name,
                'user_unsubscribe'      => $auto->user_unsubscribe,
                'trigger_integrations'  => json_decode($auto->trigger_integrations),
                'notify_admins'         => [],
            ];

            // TODO: Populate the 'notify_admins'

            foreach($tags as $tag)
            {
                $auto_array['tags'][] = [
                    'uid'   => $tag->uid,
                    'value' => $tag->value
                ];
            }

            $response['automations'][] = $auto_array;
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

        // TODO: Validation on all of these values
        $automation = [
            'name'                  => $request->get('name'),
            'active'                => $request->get('active'),
            'user_unsubscribe'      => $request->get('user_unsubscribe'),
            'trigger_integrations'  => json_encode($request->get('trigger_integrations')),
        ];

        // Validate a unique name
        $dupe_check = $page->automations()->where('name', $automation['name'])->first();
        if($dupe_check)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'You already have an automation with that name.';

            return $response;
        }

        // TODO: Handling for 'notify_admins'

        /** @var \App\Automation $automation */
        $automation = $page->automations()->create($automation);

        // Attach tags
        $tags_array = $request->get('tags');
        $tag_uids   = [];
        foreach($tags_array as $tag_item)
        {
            $tag_uids[] = $tag_item['uid'];
        }
        if(count($tag_uids))
            $automation->tags()->sync($tag_uids);

        $response['success'] = 1;
        $response['uid'] = $automation->uid;

        return $response;
    }

    /**
     * @param Request $request
     * @param $page_uid
     * @param $automation_uid
     * @return array
     */
    public function update(Request $request, $page_uid, $automation_uid)
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

        /** @var \App\Automation $automation */
        $automation = $page->automations()->where('uid', $automation_uid)->first();

        if($request->has('name'))
        {
            // Confirm/validate the name is unique
            $new_name   = $request->get('name');
            $dupe_check = $page->automations()->where('name', $new_name)->first();
            if($dupe_check && $dupe_check->uid !== $automation->uid)
            {
                $response['error'] = 1;
                $response['error_msg'] = 'You already have an automation with that name.';

                return $response;
            }

            $automation->name                   = $request->get('name');
        }

        if($request->has('active'))
        {
            $active = false;
            if($request->get('active') === true || $request->get('active') === 'true')
            {
                $active = true;
            }

            $automation->active                 = $active;
        }

        if($request->has('user_unsubscribe'))
        {
            $automation->user_unsubscribe       = $request->get('user_unsubscribe');
        }

        if($request->has('trigger_integrations'))
        {
            $automation->trigger_integrations   = json_encode($request->get('trigger_integrations'));
        }

        $automation->save();

        // TODO: Handling for 'notify_admins'

        if($request->has('tags'))
        {
            // Attach tags
            $tags_array = $request->get('tags');
            $tag_uids   = [];
            foreach($tags_array as $tag_item)
            {
                $tag_uids[] = $tag_item['uid'];
            }

            // Run a sync to sync the tags
            $automation->tags()->sync([]);
            $automation->tags()->sync($tag_uids);
        }
        
        $response['success'] = 1;

        return $response;
    }

    /**
     * @param Request $request
     * @param $page_uid
     * @param $automation_uid
     * @return array
     * @throws \Exception
     */
    public function delete(Request $request, $page_uid, $automation_uid)
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

        $automation = $page->automations()->where('uid', $automation_uid)->first();

        $automation->delete();

        $response['success'] = 1;

        return $response;
    }
}
