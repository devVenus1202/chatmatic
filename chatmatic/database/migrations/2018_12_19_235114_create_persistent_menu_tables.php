<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePersistentMenuTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('persistent_menu', function (Blueprint $table) {
            $table->increments('uid');
            $table->string('locale',30)->default("default");
            $table->boolean('composer_input_disable')->default(0);
            $table->bigInteger('page_uid')->index();
        });

        Schema::create('persistent_menu_items', function (Blueprint $table) {
            $table->increments('uid');
            $table->string('title',45);
            $table->string('type',45);
            $table->string('payload',255)->nullable();
            $table->string('url',255)->nullable();
            $table->bigInteger('parent_menu_uid')->nullable();
            $table->bigInteger('persistent_menu_uid')->index();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('persistent_menu_tables');
    }
}
