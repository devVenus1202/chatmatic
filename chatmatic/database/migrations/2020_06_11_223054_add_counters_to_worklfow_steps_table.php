<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCountersToWorklfowStepsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workflow_steps', function (Blueprint $table) {
		$table->integer('messages_delivered')->default(0);
		$table->integer('messages_read')->default(0);
		$table->integer('messages_clicked')->default(0);
		$table->integer('messages_reached')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('workflow_steps', function (Blueprint $table) {
            //
        });
    }
}
