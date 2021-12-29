<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAutomationExectutionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automation_executions', function (Blueprint $table) {
            $table->increments('uid');
            $table->bigInteger('page_uid')->index();
            $table->bigInteger('automation_uid')->index();
            $table->bigInteger('workflow_uid')->index();
            $table->bigInteger('subscriber_uid')->index();
            $table->timestamp('created_at_utc')->index();
            $table->timestamp('updated_at_utc')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('automation_executions');
    }
}
