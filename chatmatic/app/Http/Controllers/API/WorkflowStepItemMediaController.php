<?php

namespace App\Http\Controllers\API;

use App\Events\MediaObjectDeleted;
use App\WorkflowStepItemAudio;
use App\WorkflowStepItemImage;
use App\WorkflowStepItemVideo;
use Illuminate\Http\Request;

class WorkflowStepItemMediaController extends BaseController
{

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
            'url'       => null
        ];

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        // We're either accepting a URL to a media object, or a base64 string representation of that media
        // If we're getting a URL
        $file_upload    = true;
        $media_url      = null;
        if($request->has('url') && $request->get('src') === null)
        {
            $file_upload    = false;

            // Set the URL to be assigned to this model below
            $media_url      = $request->get('url');
        }

        // Build the object
        // Determine type of media
        $media_type = $request->get('type');
        switch($media_type)
        {
            case 'image':
                $storage_dir = 'images';
                $media_model = new WorkflowStepItemImage;
                $media_model->page_uid                  = $page->uid;
                $media_model->workflow_uid              = 0;
                $media_model->workflow_step_uid         = 0;
                $media_model->workflow_step_item_uid    = 0;
                $media_model->redirect_url              = '';
                $url_field = 'image_url';
                break;

            case 'audio':
                $storage_dir = 'audio';
                $media_model = new WorkflowStepItemAudio;
                $media_model->page_uid                  = $page->uid;
                $media_model->workflow_uid              = 0;
                $media_model->workflow_step_uid         = 0;
                $media_model->workflow_step_item_uid    = 0;
                $url_field = 'audio_url';
                break;

            case 'video':
                $storage_dir = 'videos';
                $media_model = new WorkflowStepItemVideo;
                $media_model->page_uid                  = $page->uid;
                $media_model->workflow_uid              = 0;
                $media_model->workflow_step_uid         = 0;
                $media_model->workflow_step_item_uid    = 0;
                $url_field = 'video_url';
                break;

            default:

                // Invalid media type provided
                $response['error']      = 1;
                $response['error_msg']  = 'Invalid "type" provided: '.$media_type.'.';

                return $response;

                break;
        }

        // If it's an uploaded file we'll handle that here, ending up with the url to the file
        if($file_upload)
        {
            // Get the file from the request
            $base64_string = $request->get('src');

            // Get the extension of the uploaded file
            $pos  = strpos($base64_string, ';');
            $type = explode(':', substr($base64_string, 0, $pos))[1];
            $ext  = explode('/', $type)[1];

            if($ext === 'jpeg')
                $ext = 'jpg';

            // Decode the base64 representation of the file
            $b64_string = explode(',', $base64_string)[1];
            $file       = base64_decode($b64_string);

            // Generate the filename
            $filename = $page->uid.'_'.\App\WorkflowStepItem::generateRandomString(5).time().\App\WorkflowStepItem::generateRandomString(5).'.'.$ext;

            // Generate the full path
            $full_path = $storage_dir.'/'.$filename;

            // Upload the file to our storage
            $upload = \Storage::disk('media')->put($full_path, $file, 'public');

            // Confirm the upload
            if( ! $upload)
            {
                // Upload failed
                $response['error']      = 1;
                $response['error_msg']  = 'Failed to upload file';

                return $response;
            }

            // Get the URL
            $media_url = \Storage::disk('media')->url($full_path);
        }

        // Set the URL
        $media_model->$url_field = $media_url;

        // Set the reference_id
        $media_model->reference_id = $request->get('reference_id');

        // Save the database representation for this media object
        $media_model->save();

        // Setup the successful response
        $response['success']    = 1;
        $response['uid']        = $media_model->uid;
        $response['url']        = $media_model->$url_field;

        return $response;
    }

    /**
     * @param Request $request
     * @param $page_uid
     * @param $media_uid
     * @return array
     * @throws \Exception
     */
    public function delete(Request $request, $page_uid, $media_uid)
    {
        $response = [
            'success'   => 0,
            'error'     => 0,
            'error_msg' => '',
            'uid'       => null,
            'url'       => null
        ];

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        // Find the media object
        $media_type = $request->get('type');
        switch($media_type)
        {
            case 'image':
                $media_model    = WorkflowStepItemImage::where('uid', $media_uid)->where('page_uid', $page->uid)->firstOrFail();
                $media_url      = $media_model->image_url;
                break;

            case 'audio':
                $media_model    = WorkflowStepItemAudio::where('uid', $media_uid)->where('page_uid', $page->uid)->firstOrFail();
                $media_url      = $media_model->audio_url;
                break;

            case 'video':
                $media_model    = WorkflowStepItemVideo::where('uid', $media_uid)->where('page_uid', $page->uid)->firstOrFail();
                $media_url      = $media_model->video_url;
                break;

            default:

                // Invalid media type provided
                $response['error']      = 1;
                $response['error_msg']  = 'Invalid "type" provided: '.$media_type.'.';

                return $response;

                break;
        }

        // Handle deleting the old file from storage

        // Strip the everything but the path
        $remove     = ['https://', 'http://'];
        $media_url  = str_ireplace($remove, '', $media_url);

        // Get the path
        $url_array  = explode('/', $media_url);
        // Remove the domain name
        unset($url_array[0]);
        // Generate file path by putting the array back together
        $file_path = '/'.implode('/', $url_array);

        // Dispatch event to handle cleanup on removed media object
        event(new MediaObjectDeleted($file_path));

        // Delete the object/record
        $media_model->delete();

        $response['success'] = 1;

        return $response;
    }
}
