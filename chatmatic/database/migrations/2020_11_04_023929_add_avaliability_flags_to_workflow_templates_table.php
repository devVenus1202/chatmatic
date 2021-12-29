<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAvaliabilityFlagsToWorkflowTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workflow_templates', function (Blueprint $table) {
            $table->boolean('to_json')->default(true);
	    $table->boolean('to_private_rep')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('workflow_templates', function (Blueprint $table) {
            //
        });
    }
}
