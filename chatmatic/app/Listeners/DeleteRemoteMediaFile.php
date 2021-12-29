<?php

namespace App\Listeners;

use App\Events\MediaObjectDeleted;
use App\WorkflowStepItemAudio;
use App\WorkflowStepItemImage;
use App\WorkflowStepItemVideo;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeleteRemoteMediaFile
{
    /**
     * Create the event listener.
     *
     * DeleteRemoteMediaFile constructor.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param MediaObjectDeleted $event
     * @throws \Exception
     */
    public function handle(MediaObjectDeleted $event)
    {
        $media_file_path = $event->media_file_path;

        // Delete the file
        \Storage::drive('media')->delete($media_file_path);
    }
}
