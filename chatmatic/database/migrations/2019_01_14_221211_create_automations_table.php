<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAutomationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automations', function (Blueprint $table) {
            $table->increments('uid');
            $table->bigInteger('page_uid')->index();
            $table->string('name');
            $table->boolean('active')->default(1);
            $table->boolean('user_unsubscribe')->default(0);
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
        Schema::dropIfExists('automations');
    }
}
