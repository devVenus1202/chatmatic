<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateZapierWebhookSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zapier_webhook_subscriptions', function (Blueprint $table) {
            $table->increments('uid');
            $table->bigInteger('page_uid')->index();
            $table->string('action');
            $table->string('target_url')->nullable();
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
        //
    }
}
