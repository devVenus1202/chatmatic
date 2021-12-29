<?php


namespace App\Traits;


use App\WorkflowTemplateStepItemAudio;
use App\WorkflowTemplateStepItemImage;
use App\WorkflowTemplateStepItemVideo;
use App\Workflow;
use Illuminate\Support\Str;

/**
 * Trait WorkflowTemplateStepItemMediaTrait
 * @package App\Traits
 */
trait WorkflowTemplateStepItemMediaTrait
{

    /**
     * @param Workflow $workflow
     * @return string
     * @throws \Exception
     */
    public function cloneMediaForWorkflow(Workflow $workflow) : string
    {
        $original_url   = '';
        $storage_dir    = '';

        // Switch the class type to get the origin url and proper storage directory
        $class          = get_class($this);
        switch($class)
        {
            case WorkflowTemplateStepItemImage::class:
                $original_url   = $this->image_url;
                $storage_dir    = 'images';
                break;

            case WorkflowTemplateStepItemAudio::class:
                $original_url   = $this->audio_url;
                $storage_dir    = 'audio';
                break;

            case WorkflowTemplateStepItemVideo::class:
                $original_url   = $this->video_url;
                $storage_dir    = 'videos';
                break;
        }

        if($original_url === '' || $storage_dir === '')
        {
            throw new \Exception('Invalid WorkflowTemplateStepItemMedia Class provided.');
        }

        // Get the original file
        $org_image      = file_get_contents($original_url);
        // Generate a new name
        $new_filename   = $workflow->uid.'_'.Str::random(22);

        // Generate the full path
        $full_path      = $storage_dir.'/'.$new_filename;

        // Upload the file to our storage
        $new_file       = \Storage::disk('media')->put($full_path, $org_image, 'public');

        // Get the URL
        $new_file_url   = \Storage::disk('media')->url($full_path);

        return $new_file_url;
    }
}