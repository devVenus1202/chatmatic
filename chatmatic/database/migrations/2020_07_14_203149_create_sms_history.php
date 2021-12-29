<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmsHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_history', function (Blueprint $table) {
        $table->bigIncrements('uid');
	    $table->string('to_phone_number');
	    $table->bigInteger('workflow_step_uid');
        $table->bigInteger('page_uid');
	    $table->timestamp('created_at_utc');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sms_history');
    }
}
