<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReferenceIdToWorkflowMediaTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workflow_step_item_images', function (Blueprint $table) {
            $table->string('reference_id', 128)->nullable()->index();
        });

        Schema::table('workflow_step_item_videos', function (Blueprint $table) {
            $table->string('reference_id', 128)->nullable()->index();
        });

        Schema::table('workflow_step_item_audio', function (Blueprint $table) {
            $table->string('reference_id', 128)->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
