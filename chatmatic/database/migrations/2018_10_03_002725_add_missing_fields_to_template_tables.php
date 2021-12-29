<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMissingFieldsToTemplateTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workflow_templates', function (Blueprint $table) {
            $table->string('keywords', 512)->default('');
            $table->boolean('archived')->default(0);
            $table->timestamp('archived_at_utc', 6)->nullable();
            $table->bigInteger('origin_workflow_uid')->nullable()->index();
        });

        Schema::table('workflow_template_steps', function (Blueprint $table) {
            $table->string('name', 64)->default('');
        });

        Schema::table('workflow_template_step_items', function (Blueprint $table) {
            $table->string('text_message', 2000)->default('');
        });


        Schema::table('workflow_template_step_item_map', function (Blueprint $table) {
            $table->string('type', 32)->default('');
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
