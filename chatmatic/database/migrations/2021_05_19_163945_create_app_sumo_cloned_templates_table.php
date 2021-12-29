<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppSumoClonedTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_sumo_cloned_templates', function (Blueprint $table) {
            $table->increments('uid');
            $table->bigInteger('user_uid');
            $table->bigInteger('page_uid');
            $table->bigInteger('template_uid');
            $table->boolean('paid_template')->default(false);
            $table->timestamp('created_at_utc');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('app_sumo_cloned_templates');
    }
}
