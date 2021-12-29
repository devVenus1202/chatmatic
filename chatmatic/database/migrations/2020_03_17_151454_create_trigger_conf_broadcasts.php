<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTriggerConfBroadcasts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trigger_conf_broadcasts', function (Blueprint $table) {
            $table->increments('uid')->index();
	    $table->string('broadcast_type',32)->nullable();
	    $table->string('intention',255)->nullable();
	    $table->datetime('start_time_utc')->nullable();
	    $table->datetime('end_time_utc')->nullable();
	    $table->string('conditions_json',1042)->nullable();
	    $table->integer('status');
	    $table->string('facebook_messaging_type',32)->nullable();
            $table->string('facebook_messaging_tag',32)->nullable();
	    $table->datetime('fire_at_utc')->nullable();
	    $table->integer('optimized');
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
        Schema::dropIfExists('trigger_conf_broadcasts');
    }
}
