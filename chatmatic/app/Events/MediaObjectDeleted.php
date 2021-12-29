<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class MediaObjectDeleted
{
    use SerializesModels;

    public $media_file_path;

    /**
     * Create a new event instance.
     *
     * @param $media_file_path
     * @return void
     */
    public function __construct($media_file_path)
    {
        $this->media_file_path = $media_file_path;
    }
}
