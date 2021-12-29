<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIntegrationRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integration_records', function (Blueprint $table) {
            $table->increments('uid');
            $table->bigInteger('integration_uid')->index();
            $table->bigInteger('integration_type_uid')->index();
            $table->bigInteger('page_uid')->index();
            $table->boolean('success')->default(false)->index();
            $table->text('payload')->default('[]');
            $table->text('response')->default('[]');
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
        //
    }
}
