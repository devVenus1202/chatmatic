<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkflowStepOptionDelaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workflow_step_option_delays', function (Blueprint $table) {
		$table->bigIncrements('uid');   
		$table->string('time_unit')->nullable();       
        $table->integer('amount')->nullable();
        $table->dateTime('fire_until',0)->nullable();
        $table->string('type')->default('remaining');
        $table->bigInteger('next_step_uid')->index();
		$table->bigInteger('workflow_step_uid')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workflow_step_option_delays');
    }
}
