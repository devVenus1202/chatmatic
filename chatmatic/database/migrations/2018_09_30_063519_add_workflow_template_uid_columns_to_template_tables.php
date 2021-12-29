<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWorkflowTemplateUidColumnsToTemplateTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workflow_template_step_items', function (Blueprint $table) {
            $table->bigInteger('workflow_template_uid')->index();
        });

        Schema::table('workflow_template_step_item_map', function (Blueprint $table) {
            $table->bigInteger('workflow_template_uid')->index();
        });

        Schema::table('workflow_template_step_item_images', function (Blueprint $table) {
            $table->bigInteger('workflow_template_uid')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('workflow_template_step_items', function (Blueprint $table) {
            $table->dropColumn('workflow_template_uid');
        });

        Schema::table('workflow_template_step_item_map', function (Blueprint $table) {
            $table->dropColumn('workflow_template_uid');
        });

        Schema::table('workflow_template_step_item_images', function (Blueprint $table) {
            $table->dropColumn('workflow_template_uid');
        });
    }
}
