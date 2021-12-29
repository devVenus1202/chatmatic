<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatmaticUserFollowingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chatmatic_user_followings', function (Blueprint $table) {
            $table->increments('uid');
            $table->bigInteger('followee_uid');
            $table->bigInteger('follower_uid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chatmatic_user_followings');
    }
}
