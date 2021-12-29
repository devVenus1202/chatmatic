<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomFieldTemplateUidToQuickReplyTemplateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workflow_template_step_quick_replies', function (Blueprint $table) {
            $table->bigInteger('custom_field_template_uid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('workflow_template_step_quick_replies', function (Blueprint $table) {
            //
        });
    }
}
