<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTriggerConfTriggersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trigger_conf_triggers', function (Blueprint $table) {
            $table->bigIncrements('uid');
            $table->string('message', 960);
            $table->bigInteger('comments');
            $table->bigInteger('messages_sent');
            $table->bigInteger('post_uid')->index();
            $table->smallInteger('active')->default(1);
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
        Schema::dropIfExists('trigger_conf_triggers');
    }
}
