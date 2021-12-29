<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTagTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tag_templates', function (Blueprint $table) {
		$table->bigIncrements('uid');
		$table->string('value');
		$table->bigInteger('workflow_template_uid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tag_templates', function (Blueprint $table) {
            //
        });
    }
}
