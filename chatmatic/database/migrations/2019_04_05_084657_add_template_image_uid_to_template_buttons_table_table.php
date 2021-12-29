<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTemplateImageUidToTemplateButtonsTableTable extends Migration
{
    public $column_name = 'workflow_template_step_item_image_uid';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workflow_template_step_item_map', function (Blueprint $table) {
            $table->bigInteger($this->column_name)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('workflow_template_step_item_map', function (Blueprint $table) {
            $table->dropColumn($this->column_name);
        });
    }
}
