<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApiErrorLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_error_log', function (Blueprint $table) {
            $table->increments('uid');
            $table->bigInteger('page_uid')->index();
            $table->bigInteger('workflow_uid')->index();
            $table->boolean('resolved')->default(false);
            $table->longText('error_msg')->default('');
            $table->longText('request')->nullable();
            $table->longText('response')->nullable();
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
        Schema::dropIfExists('api_error_log');
    }
}
