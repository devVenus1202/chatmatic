<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkflowTriggersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workflow_triggers', function (Blueprint $table) {
             $table->increments('uid')->index();
             $table->string('type', 32)->default('welcomemsg');
             $table->string('name', 64);
             $table->integer('messages_delivered');
             $table->integer('messages_read');
             $table->integer('messages_clicked');
             $table->integer('conversions');
             $table->boolean('archived')->nullable();
             $table->datetime('archived_at_utc')->nullable();
             $table->datetime('created_at_utc');
             $table->datetime('updated_at_utc');
             $table->bigInteger('page_uid')->index();
             $table->bigInteger('workflow_uid')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workflows_triggers');
    }
}
