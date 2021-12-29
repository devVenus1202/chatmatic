<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveCustomFieldUidFromWorkflowsStepsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->dropColumn('custom_field_uid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->integer('custom_field_uid');
        });
    }
}
