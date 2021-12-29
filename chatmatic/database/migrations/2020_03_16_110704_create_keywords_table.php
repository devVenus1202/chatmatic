<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKeywordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trigger_conf_keywords', function (Blueprint $table) {
            $table->increments('uid')->index();
	    $table->string('words',512);
	    $table->string('option',32)->default('contains_any');
	    $table->bigInteger('workflow_trigger_uid')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('keywords');
    }
}
