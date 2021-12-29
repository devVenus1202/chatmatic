<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMMesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trigger_conf_m_dot_mes', function (Blueprint $table) {
            $table->increments('uid')->index();
	    $table->string('public_id',255);
	    $table->string('m_me_url',3000);
	    $table->string('custom_ref',255)->nullable();
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
        Schema::dropIfExists('m_mes');
    }
}
