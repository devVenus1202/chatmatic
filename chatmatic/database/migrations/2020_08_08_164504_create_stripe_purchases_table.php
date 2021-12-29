<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStripePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stripe_purchases', function (Blueprint $table) {
            $table->bigIncrements('uid');
            $table->string('type');
            $table->float('total');
            $table->bigInteger('chatmatic_buyer_uid');
            $table->bigInteger('chatmatic_seller_uid')->nullable();
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
        Schema::dropIfExists('stripe_purchases');
    }
}
