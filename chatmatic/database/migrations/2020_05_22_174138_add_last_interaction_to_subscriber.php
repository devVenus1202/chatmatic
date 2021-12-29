<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLastInteractionToSubscriber extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->integer('last_out_interaction')->nullable()->index();
            $table->integer('last_out_auto_response_interaction')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->dropColumn('last_out_interaction');
            $table->dropColumn('last_out_auto_response_interaction');
        });
    }
}
