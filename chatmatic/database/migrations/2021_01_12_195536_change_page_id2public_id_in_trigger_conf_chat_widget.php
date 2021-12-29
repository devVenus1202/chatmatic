<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangePageId2publicIdInTriggerConfChatWidget extends Migration
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
            $table->renameColumn('page_id', 'public_id');
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
            $table->renameColumn('public_id', 'page_id');
        });
    }
}
