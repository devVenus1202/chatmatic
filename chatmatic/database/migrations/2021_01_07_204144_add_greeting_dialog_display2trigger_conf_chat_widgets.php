<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGreetingDialogDisplay2triggerConfChatWidgets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trigger_conf_chat_widgets', function (Blueprint $table) {
            //
            $table->string('greeting_dialog_display')->default('show');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trigger_conf_chat_widgets', function (Blueprint $table) {
            //
        });
    }
}
