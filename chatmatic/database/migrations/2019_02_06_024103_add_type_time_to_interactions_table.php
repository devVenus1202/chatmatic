<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeTimeToInteractionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('interactions', function (Blueprint $table) {
            $table->bigInteger('workflow_step_uid')->nullable()->index();
            $table->bigInteger('campaign_uid')->nullable()->index();
            $table->string('type',64)->nullable();
            $table->timestamp('created_at_utc')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('interactions', function (Blueprint $table) {
            //
        });
    }
}
