<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomFieldResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_field_responses', function (Blueprint $table) {
            $table->bigIncrements('uid');
            $table->text('response');
            $table->bigInteger('custom_field_uid')->index();  
            $table->bigInteger('subscriber_uid')->index();    
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('custom_field_responses');
    }
}
