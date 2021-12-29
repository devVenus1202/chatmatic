<?php

namespace App\Http\Controllers\API;

use App\CustomField;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CustomFieldController extends BaseController
{

    /**
     * @param Request $request
     * @param $page_uid
     * @return \App\Page|array
     */
    public function index(Request $request, $page_uid)
    {
        $response = [
            'success'       => 0,
            'error'         => 0,
            'error_msg'     => '',
            'custom_fields' => [],
        ];

        $user = $this->user;
        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        $custom_fields = $page->customFields()->where('archived', false)->orderBy('uid', 'asc')->get();

        foreach($custom_fields as $custom_field)
        {
            $response['custom_fields'][] = [
                'uid'               => $custom_field->uid,
                'field_name'        => $custom_field->field_name,
                'validation_type'   => $custom_field->validation_type,
                'page_uid'          => $custom_field->page_uid,
                'merge_tag'         => $custom_field->merge_tag,
                'field_type'        => $custom_field->custom_field_type,
                'default_value'     => $custom_field->default_value,
                'count'             => $custom_field->customFieldResponses()->count() // subscribers
            ];
        }

        $response['success'] = 1;

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
            'custom_field'  => [],
        ];

        $user = $this->user;
        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        $field_name             = $request->get('field_name');
        $field_type             = $request->get('field_type');
        $field_default_value    = null;
        if($request->has('default_value'))
            $field_default_value    = $request->get('default_value');

        // Check for invalid custom field names
        $invalid_names = ['name', 'email'];
        if(in_array($field_name, $invalid_names, false))
        {
            $response['error'] = 1;
            $response['error_msg'] = 'The following Custom Field names are invalid: '.implode(', ', $invalid_names).'. Please choose another.';

            return $response;
        }

        $validation_type    = $request->get('validation_type');
        $merge_tag          = '{'.strtolower(str_replace('-', '_', str_slug($field_name))).'}';

        // Validate length of field name
        // 58 instead of 60 because we're tacking on the {} for the merge field which has the same 60 character limit
        if(mb_strlen($field_name) > 58)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Custom Field name is too long. Maximum character length is 58 characters, current attempted name is '.mb_strlen($field_name).' characters.';

            return $response;
        }

        $custom_field['field_name']         = $field_name;
        $custom_field['validation_type']    = $validation_type;
        $custom_field['page_uid']           = $page->uid;
        $custom_field['merge_tag']          = $merge_tag;
        $custom_field['custom_field_type']  = $field_type;
        $custom_field['default_value']      = $field_default_value;
        $custom_field['archived']           = false;

        // Confirm there aren't any existing custom fields with this same merge tag
        $dupe_check = CustomField::where('page_uid', $page->uid)->where('merge_tag', $merge_tag)->first();
        if($dupe_check)
        {
            // Looks like there's a dupe - we'll attempt to resolve by adding a 0 to the end of the merge_tag
            $merge_tag   = str_replace(['{', '}'], '', $merge_tag);
            $merge_tag   = '{'.$merge_tag.'0'.'}';

            $dupe_check = CustomField::where('page_uid', $page->uid)->where('merge_tag', $merge_tag)->first();

            if($dupe_check)
            {
                $response['error'] = 1;
                $response['error_msg'] = 'Duplicate merge tag generated with given custom field name, please choose a more unique custom field name.';

                return $response;
            }
        }
        unset($dupe_check);

        // Confirm there aren't any custom fields with this same name
        $dupe_check = CustomField::where('page_uid', $page->uid)->where('field_name', $field_name)->first();
        if($dupe_check)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Duplicate custom field name, please choose a unique name.';

            return $response;
        }

        // Confirm the validation type
        if( ! in_array($validation_type, ['general', 'date', 'text', 'number']))
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Invalid value for validation type.';

            return $response;
        }

        // Confirm the field type
        if( ! in_array($field_type, ['set_value', 'increment']))
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Invalid value for custom field type.';

            return $response;
        }

        $custom_field = CustomField::create($custom_field);

        $response['custom_field'] = [
            'uid'               => $custom_field->uid,
            'field_name'        => $custom_field->field_name,
            'validation_type'   => $custom_field->validation_type,
            'page_uid'          => $custom_field->page_uid,
            'merge_tag'         => $custom_field->merge_tag,
            'field_type'        => $custom_field->custom_field_type,
            'default_value'     => $custom_field->default_value
        ];
        $response['success'] = 1;

        return $response;
    }

    /**
     * @param Request $request
     * @param $page_uid
     * @param $custom_field_uid
     * @return array
     */
    public function update(Request $request, $page_uid, $custom_field_uid)
    {
        $response = [
            'success'       => 0,
            'error'         => 0,
            'error_msg'     => '',
            'custom_field'  => [],
        ];

        $user = $this->user;
        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        /** @var \App\CustomField $custom_field */
        $custom_field = $page->customFields()->where('uid', $custom_field_uid)->first();

        if( ! $custom_field)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Custom Field with uid '.$custom_field_uid.' not found.';

            return $response;
        }

        $field_name             = $request->get('field_name');
        $field_type             = $request->get('field_type');
        $field_default_value    = $custom_field->default_value;
        if($request->has('default_value'))
            $field_default_value    = $request->get('default_value');

        // Check for invalid custom field names
        $invalid_names = ['name', 'email'];
        if(in_array($field_name, $invalid_names, false))
        {
            $response['error'] = 1;
            $response['error_msg'] = 'The following Custom Field names are invalid: '.implode(', ', $invalid_names).'. Please choose another.';

            return $response;
        }

        $validation_type    = $request->get('validation_type');
        $merge_tag          = '{'.strtolower(str_replace('-', '_', str_slug($field_name))).'}';

        // Validate length of field name
        // 58 instead of 60 because we're tacking on the {} for the merge field which has the same 60 character limit
        if(mb_strlen($field_name) > 58)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Custom Field name is too long. Maximum character length is 58 characters, current attempted name is '.mb_strlen($field_name).' characters.';

            return $response;
        }

        // Confirm there aren't any existing custom fields with this same merge tag
        $dupe_check = CustomField::where('page_uid', $page->uid)->where('merge_tag', $merge_tag)->first();
        if($dupe_check)
        {
            // Looks like there's a dupe - we'll attempt to resolve by adding a 0 to the end of the merge_tag
            $merge_tag   = str_replace(['{', '}'], '', $merge_tag);
            $merge_tag   = '{'.$merge_tag.'0'.'}';

            $dupe_check = CustomField::where('page_uid', $page->uid)->where('merge_tag', $merge_tag)->first();

            if($dupe_check)
            {
                $response['error'] = 1;
                $response['error_msg'] = 'Duplicate merge tag generated with given custom field name, please choose a more unique custom field name.';

                return $response;
            }
        }
        unset($dupe_check);

        // Confirm there aren't any custom fields with this same name
        $dupe_check = CustomField::where('page_uid', $page->uid)->where('field_name', $field_name)->first();
        if($dupe_check && $dupe_check->uid !== $custom_field->uid)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Duplicate custom field name, please choose a unique name.';

            return $response;
        }

        // Confirm the validation type
        if( ! in_array($validation_type, ['general', 'date', 'text', 'number']))
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Invalid value for validation type.';

            return $response;
        }

        // Confirm the field type
        if( ! in_array($field_type, ['set_value', 'increment']))
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Invalid value for custom field type.';

            return $response;
        }

        $custom_field->custom_field_type    = $field_type;
        $custom_field->field_name           = $field_name;
        $custom_field->validation_type      = $validation_type;
        $custom_field->merge_tag            = $merge_tag;
        $custom_field->default_value        = $field_default_value;
        $custom_field->save();

        $response['success'] = 1;

        $response['custom_field'] = [
            'uid'               => $custom_field->uid,
            'field_name'        => $custom_field->field_name,
            'validation_type'   => $custom_field->validation_type,
            'page_uid'          => $custom_field->page_uid,
            'merge_tag'         => $custom_field->merge_tag,
            'field_type'        => $custom_field->custom_field_type,
            'default_value'     => $custom_field->default_value
        ];

        return $response;
    }

    /**
     * @param Request $request
     * @param $page_uid
     * @param $custom_field_uid
     * @return \App\Page|array
     */
    public function delete(Request $request, $page_uid, $custom_field_uid)
    {
        $response = [
            'success'       => 0,
            'error'         => 0,
            'error_msg'     => '',
            'custom_field'  => [],
        ];

        $user = $this->user;
        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        /** @var \App\CustomField $custom_field */
        $custom_field = $page->customFields()->where('uid', $custom_field_uid)->first();

        if( ! $custom_field)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Custom Field with uid '.$custom_field_uid.' not found.';

            return $response;
        }

        $custom_field->archived         = true;
        $custom_field->archived_at_utc  = Carbon::now();
        $custom_field->save();

        $response['success'] = 1;

        return $response;
    }


    /**
     * @param Request $request
     * @param $page_uid
     * @param $custom_field_uid
     * @return \App\Page|array
     */
    public function subscribers(Request $request, $page_uid, $custom_field_uid)
    {
        $response = [
            'success'       => 0,
            'error'         => 0,
            'error_msg'     => '',
            'subscribers'   => []
        ];

        $user = $this->user;
        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        /** @var \App\CustomField $custom_field */
        $custom_field = $page->customFields()->where('uid', $custom_field_uid)->first();

        foreach($custom_field->customFieldResponses()->get() as $cf_response)
        {   
            $subscriber = $cf_response->subscriber()->first();

            $response['subscribers'][] = [
                'names'         => $subscriber->first_name.' '.$subscriber->last_name,
                'profile_pic'   => $subscriber->profile_pic_url,
                'response'      => $cf_response->response,
            ];
            
        }

        $response['success'] = 1;

        return $response;
    }
}
