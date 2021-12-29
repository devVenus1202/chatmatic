<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTemplateCustomFieldTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_field_templates', function (Blueprint $table) {
	    $table->bigIncrements('uid');
	    $table->string('field_name', 60);
	    $table->string('validation_type', 45);
	    $table->bigInteger('template_uid')->index();
	    $table->string('merge_tag',60);
	    $table->string('custom_field_type', 60);
	    $table->string('default_value',60)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('custom_field_templates');
    }
}
