<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatmaticUserProfileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chatmatic_user_profiles', function (Blueprint $table) {
            $table->increments('uid');
            $table->bigInteger('chatmatic_user_uid');
            $table->text('description', 4096)->nullable();
            $table->string('facebook_url', 255)->nullable();
            $table->string('twitter_url', 255)->nullable();
            $table->string('linkedin_url', 255)->nullable();
            $table->string('youtube_url', 255)->nullable();
            $table->string('other_url', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chatmatic_user_profiles');
    }
}
