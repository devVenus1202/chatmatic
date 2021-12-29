<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkflowTemplateStepQuickRepliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workflow_template_step_quick_replies', function (Blueprint $table) {
            $table->bigIncrements('uid');
            $table->bigInteger('workflow_template_uid')->index();
            $table->bigInteger('workflow_template_step_uid')->index();
            $table->string('type', 24);
            $table->integer('map_order')->default(0);
            $table->string('map_text', 32);
            $table->string('map_action', 32);
            $table->string('map_action_text', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workflow_template_quick_replies');
    }
}
