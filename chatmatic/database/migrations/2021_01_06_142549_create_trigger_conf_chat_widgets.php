<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTriggerConfChatWidgets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trigger_conf_chat_widgets', function (Blueprint $table) {
            $table->increments('uid')->index();
            $table->string('page_id',255);
            $table->string('color');
            $table->string('log_in_greeting');
            $table->string('log_out_greeting');
            $table->string('delay');
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
        Schema::dropIfExists('trigger_conf_chat_widgets');
    }
}
