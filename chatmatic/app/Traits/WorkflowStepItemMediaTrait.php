<?php


namespace App\Traits;


use App\WorkflowStepItemAudio;
use App\WorkflowStepItemImage;
use App\WorkflowStepItemVideo;
use App\WorkflowTemplate;
use Illuminate\Support\Str;

/**
 * Trait WorkflowStepItemMediaTrait
 * @package App\Traits
 */
trait WorkflowStepItemMediaTrait
{

    /**
     * @param WorkflowTemplate $workflowTemplate
     * @return string
     * @throws \Exception
     */
    public function cloneMediaForTemplate(WorkflowTemplate $workflowTemplate) : string
    {
        $original_url   = '';
        $storage_dir    = '';

        // Switch the class type to get the origin url and proper storage directory
        $class          = get_class($this);
        switch($class)
        {
            case WorkflowStepItemImage::class:
                $original_url   = $this->image_url;
                $storage_dir    = 'images';
                break;

            case WorkflowStepItemAudio::class:
                $original_url   = $this->audio_url;
                $storage_dir    = 'audio';
                break;

            case WorkflowStepItemVideo::class:
                $original_url   = $this->video_url;
                $storage_dir    = 'videos';
                break;
        }

        if($original_url === '' || $storage_dir === '')
        {
            throw new \Exception('Invalid WorkflowStepItemMedia Class provided.');
        }

        // Get the original file
        $org_image      = file_get_contents($original_url);
        // Generate a new name
        $new_filename   = 'template'.$workflowTemplate->uid.'_'.Str::random(22);

        // Generate the full path
        $full_path      = $storage_dir.'/'.$new_filename;

        // Upload the file to our storage
        $new_file       = \Storage::disk('media')->put($full_path, $org_image, 'public');

        // Get the URL
        $new_file_url   = \Storage::disk('media')->url($full_path);

        return $new_file_url;
    }
}