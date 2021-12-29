<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSusbscriberClickHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriber_click_history', function (Blueprint $table) {
            $table->bigIncrements('uid');
	    $table->bigInteger('subscriber_uid')->index();
	    $table->bigInteger('workflow_uid');
	    $table->bigInteger('workflow_step_uid')->index();
	    $table->string('click_type',45);
	    $table->bigInteger('element_uid');
            $table->timestamp('created_at_utc');
	    $table->timestamp('updated_at_utc');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscribers_click_history');
    }
}
