<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTriggerConfButtons extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trigger_conf_buttons', function (Blueprint $table) {
            $table->increments('uid')->index();
            $table->string('public_id',255);
	    $table->string('postsubmit_redirect_url',255);
	    $table->string('color')->default('blue');
	    $table->string('size')->default('standard');
	    $table->bigInteger('workflow_trigger_uid')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trigger_conf_buttons');
    }
}
