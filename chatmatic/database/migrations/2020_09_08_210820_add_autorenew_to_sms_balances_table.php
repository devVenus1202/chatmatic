<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAutorenewToSmsBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sms_balances', function (Blueprint $table) {
            $table->bigInteger('page_uid')->nullable()->unique();
            $table->boolean('autorenew')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sms_balances', function (Blueprint $table) {
            //
        });
    }
}
