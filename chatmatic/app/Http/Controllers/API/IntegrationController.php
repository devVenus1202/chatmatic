<?php

namespace App\Http\Controllers\API;

use App\IntegrationType;
use App\Page;
use Illuminate\Http\Request;

class IntegrationController extends BaseController
{
    /**
     * @param Request $request
     * @return array
     */
    public function index(Request $request, $page_uid)
    {
        $response = [
            'success'       => 0,
            'error'         => 0,
            'error_msg'     => 0,
            'integrations'  => []
        ];

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        $integrations = $page->integrations()->orderBy('uid')->get();

        $return_integrations = [];
        /** @var \App\Integration $integration */
        foreach($integrations as $integration)
        {
            $return_integrations[] = [
                'uid'                   => $integration->uid,
                'integration_type_uid'  => $integration->integration_type_uid,
                'page_uid'              => $integration->page_uid,
                'active'                => $integration->active,
                'name'                  => $integration->name,
                'parameters'            => json_decode($integration->parameters),
                'triggered'             => $integration->integrationRecords()->count(),
            ];
        }

        $response['success'] = 1;
        $response['integrations'] = $return_integrations;

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
            'error_msg'     => 0,
            'integration'   => []
        ];

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        $integration_type_uid   = $request->get('integration_type_uid');
        $integration_parameters = $request->get('parameters');
        $integration_active     = $request->get('active');
        $integration_name       = $request->get('name');

        // TODO: Confirm the parameters match those required by the IntegrationType

        $integration = [
            'integration_type_uid'  => $integration_type_uid,
            'parameters'            => json_encode($integration_parameters, JSON_UNESCAPED_SLASHES),
            'active'                => $integration_active,
            'name'                  => $integration_name,
        ];

        /** @var \App\Integration $integration */
        $integration = $page->integrations()->create($integration);

        $response['success']        = 1;
        $response['integration']    = [
            'uid'                   => $integration->uid,
            'integration_type_uid'  => $integration->integration_type_uid,
            'parameters'            => json_decode($integration->parameters),
            'active'                => $integration->active,
            'name'                  => $integration->name,
            'triggered'             => 0,
        ];

        return $response;
    }

    /**
     * @param Request $request
     * @param $page_uid
     * @param $integration_uid
     * @return array
     */
    public function update(Request $request, $page_uid, $integration_uid)
    {
        $response = [
            'success'       => 0,
            'error'         => 0,
            'error_msg'     => 0,
            'integration'   => []
        ];

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        /** @var \App\Integration $integration */
        $integration = $page->integrations()->where('uid', $integration_uid)->first();

        if( ! $integration)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Integration not found';

            return $response;
        }

        if($request->has('parameters'))
            $parameters = $request->get('parameters');
        if($request->has('active'))
            $active     = $request->get('active');
        if($request->has('name'))
            $name       = $request->get('name');

        if($request->has('parameters'))
            $integration->parameters    = json_encode($parameters, JSON_UNESCAPED_SLASHES);
        if($request->has('active'))
            $integration->active        = $active;
        if($request->has('name'))
            $integration->name          = $name;

        $integration->save();

        $response['success'] = 1;
        $response['integration'] = [
            'uid'                   => $integration->uid,
            'integration_type_uid'  => $integration->integration_type_uid,
            'parameters'            => json_decode($integration->parameters),
            'active'                => $integration->active,
            'name'                  => $integration->name,
            'triggered'             => $integration->integrationRecords()->count(),
        ];

        return $response;
    }

    /**
     * @param Request $request
     * @param $page_uid
     * @param $integration_uid
     * @return array
     * @throws \Exception
     */
    public function delete(Request $request, $page_uid, $integration_uid)
    {
        $response = [
            'success'       => 0,
            'error'         => 0,
            'error_msg'     => 0,
        ];

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        /** @var \App\Integration $integration */
        $integration = $page->integrations()->where('uid', $integration_uid)->first();

        if( ! $integration)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Integration not found';

            return $response;
        }

        // Delete integration records
        $integration->integrationRecords()->delete();

        $integration->delete();

        $response['success'] = 1;

        return $response;
    }
}
