<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaggableTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('taggable_templates', function (Blueprint $table) {
        $table->bigInteger('tag_template_uid')->index();
	    $table->bigInteger('taggable_template_uid')->index();
	    $table->string('taggable_type',64);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('taggable_templates');
    }
}
