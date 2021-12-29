<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAttachmentIdToMediaTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workflow_step_item_images', function (Blueprint $table) {
            $table->string('fb_attachment_id')->nullable();
        });

        Schema::table('workflow_step_item_videos', function (Blueprint $table) {
            $table->string('fb_attachment_id')->nullable();
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
