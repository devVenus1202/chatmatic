<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewFieldsToWorkflowTemplateSteps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workflow_template_steps', function (Blueprint $table) {
             $table->bigInteger('child_step_uid')->nullable();
             $table->float('x_pos', 3,2)->default(0.0);
             $table->float('y_pos', 3,2)->default(0.0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('workflow_template_steps', function (Blueprint $table) {
            //
        });
    }
}
