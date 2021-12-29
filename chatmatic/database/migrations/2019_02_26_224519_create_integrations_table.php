<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIntegrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integrations', function (Blueprint $table) {
            $table->increments('uid');
            $table->bigInteger('integration_type_uid')->index();
            $table->bigInteger('page_uid')->index();
            $table->text('parameters')->default('[]');
            $table->boolean('active')->default(false);
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
