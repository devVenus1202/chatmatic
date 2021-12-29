<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppSumoUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_sumo_users', function (Blueprint $table) {
            $table->bigIncrements('uid');
            $table->string('email',35);
            $table->string('plan_id',20);
            $table->string('uuid',50);
            $table->string('invoice_item_uuid',50);
            $table->integer('used_licenses');
            $table->integer('cloned_templates');
            $table->boolean('refunded')->default(false);
            $table->bigInteger('chatmatic_user_id')->nullable();
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
        Schema::dropIfExists('app_sumo_users');
    }
}
