<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PopulateExistingOutboundLinkUidsOnButtonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @throws Exception
     */
    public function up()
    {
        $buttons = \App\WorkflowStepItemMap::where('map_action', 'web_url')->whereNull('outbound_link_uid')->get();

        // Populate WorkflowStepItemMap OutboundLink records
        foreach($buttons as $key => $button)
        {
            // Generate a link
            $link = \App\OutboundLink::findOrCreateNewLink($button->map_action_text, $button->uid);
            $button->outbound_link_uid = $link->uid;
            $button->save();

            if($key % 5000)
            {
                echo 'Updated through '.$key.' out of '.count($buttons).' records.'.PHP_EOL;
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
