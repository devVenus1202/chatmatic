<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkflowTemplateStepOptionRandomizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workflow_template_step_option_randomizations', function (Blueprint $table) {
            $table->bigIncrements('uid');   
            $table->string('name');       
            $table->integer('probability');
            $table->bigInteger('workflow_template_next_step_uid')->index();
            $table->bigInteger('workflow_template_step_uid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workflow_template_step_option_randomizations');
    }
}
