<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameParentUidOnStepTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workflow_steps', function(Blueprint $table) {
            $table->renameColumn('parent_step_uid', 'child_step_uid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('workflow_steps', function(Blueprint $table) {
            $table->renameColumn('child_step_uid', 'parent_step_uid');
        });
    }
}
