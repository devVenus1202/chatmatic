<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkflowStepItemAudiosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workflow_step_item_audios', function (Blueprint $table) {
              $table->increments('uid');      
              $table->bigInteger('workflow_step_item_uid')->index();
              $table->string('audio_url',255);
              $table->bigInteger('page_uid'); 
              $table->bigInteger('workflow_uid');
              $table->bigInteger('wordflow_step_uid');
          });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workflow_step_item_audios');
    }
}
