<?php

namespace App\Http\Controllers\API;

use App\IntegrationType;
use Illuminate\Http\Request;

class IntegrationTypeController extends BaseController
{
    /**
     * @param Request $request
     * @return array
     */
    public function index(Request $request)
    {
        $response = [
            'success'   => 0,
            'error'     => 0,
            'error_msg' => 0,
            'types'     => []
        ];

        $integration_types = IntegrationType::where('active', true)->orderBy('uid')->get();

        $return_types = [];
        /** @var \App\IntegrationType $integration_type */
        foreach($integration_types as $integration_type)
        {
            $return_types[] = [
                'uid'           => $integration_type->uid,
                'name'          => $integration_type->name,
                'slug'          => $integration_type->slug,
                'parameters'    => json_decode($integration_type->parameters)
            ];
        }

        $response['success'] = 1;
        $response['types'] = $return_types;

        return $response;
    }
}
