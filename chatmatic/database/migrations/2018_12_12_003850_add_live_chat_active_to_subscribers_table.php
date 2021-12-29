<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLiveChatActiveToSubscribersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscribers', function (Blueprint $table) {

            // Add 'live_chat_active' field - the UI will send an initial request to turn this to true when webhook events for this
            // subscriber + page combination should be forwarded to the UI via websockets for real-time chat updates. This field will also
            // be toggled to true on every heartbeat request (as below).
            $table->boolean('live_chat_active')->default(0);
            // Add 'live_chat_heartbeat_utc' field - The UI will send an occasional heartbeat request to update this field
            $table->timestamp('live_chat_heartbeat_utc', 6)->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
