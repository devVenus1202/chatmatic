<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReplaceOldCdnUrlsOnWorkflowImages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $environment = App::environment();
        if($environment  === 'staging')
            $images = \App\WorkflowStepItemImage::where('image_url', 'ILIKE', 'https://cdns%')->get();
        else
            $images = \App\WorkflowStepItemImage::where('image_url', 'ILIKE', 'https://cdnp%')->get();

        foreach($images as $image)
        {
            if($environment === 'staging')
                $new_url = str_replace('cdns.chatmatic.info', 'test.chatmatic.com', $image->image_url);
            else
                $new_url = str_replace('cdnp.chatmatic.info', 'live.chatmatic.com', $image->image_url);
            $image->image_url = $new_url;
            $image->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
