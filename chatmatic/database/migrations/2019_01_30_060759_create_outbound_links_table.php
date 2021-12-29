<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOutboundLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outbound_links', function (Blueprint $table) {
            $table->increments('uid');
            $table->bigInteger('page_uid');
            $table->bigInteger('workflow_uid');
            $table->bigInteger('workflow_step_uid');
            $table->bigInteger('workflow_step_item_uid');
            $table->bigInteger('workflow_step_item_map_uid')->nullable();
            $table->bigInteger('workflow_step_item_image_uid')->nullable();
            $table->string('url', 255);
            $table->string('slug', 128)->index();
            $table->bigInteger('redirect_count')->default(0);
        });
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
