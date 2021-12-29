<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PopulateExistingOutboundLinkUidsOnImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @throws Exception
     * @return void
     */
    public function up()
    {
        $images = \App\WorkflowStepItemImage::whereNull('outbound_link_uid')->get();

        // Populate WorkflowStepItemImage OutboundLink records
        foreach($images as $image)
        {
            if($image->redirect_url !== '')
            {
                // Generate a link
                $link = \App\OutboundLink::findOrCreateNewLink($image->redirect_url, null, $image->uid);
                $image->outbound_link_uid = $link->uid;
                $image->save();
            }
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
