<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkflowTemplateStepItemAudioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workflow_template_step_item_audio', function (Blueprint $table) {
            $table->bigIncrements('uid');
            $table->bigInteger('workflow_template_uid')->index();
            $table->bigInteger('workflow_template_step_item_uid')->index();
            $table->string('audio_url', 255);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workflow_template_step_item_audio');
    }
}
