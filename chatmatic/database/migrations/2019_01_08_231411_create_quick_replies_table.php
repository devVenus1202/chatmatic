<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuickRepliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quick_replies', function (Blueprint $table) {
            $table->increments('uid');
            $table->bigInteger('page_uid')->index();
            $table->bigInteger('workflow_uid')->index();
            $table->bigInteger('workflow_step_uid')->index();
            $table->bigInteger('automation_uid')->nullable()->index();
            $table->string('type', 24);
            $table->integer('map_order')->default(0);
            $table->string('map_text', 32);
            $table->string('map_action', 32);
            $table->string('map_action_text', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quick_replies');
    }
}
