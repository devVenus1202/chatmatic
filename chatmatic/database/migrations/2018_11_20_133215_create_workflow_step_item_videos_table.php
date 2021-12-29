<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkflowStepItemVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workflow_step_item_videos', function (Blueprint $table) {
            $table->increments('uid');
            $table->bigInteger('workflow_step_item_uid')->index();
            $table->string('video_url', 255);
            $table->bigInteger('page_uid');
            $table->bigInteger('workflow_uid');
            $table->bigInteger('workflow_step_uid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workflow_step_item_videos');
    }
}
