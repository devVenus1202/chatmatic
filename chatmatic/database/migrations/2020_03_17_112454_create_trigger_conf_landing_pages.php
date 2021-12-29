<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTriggerConfLandingPages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trigger_conf_landing_pages', function (Blueprint $table) {
            $table->increments('uid')->index();
            $table->string('public_id',255);
	    $table->string('presubmit_title',255);
	    $table->string('presubmit_body',2048);
	    $table->string('presubmit_image',255);
	    $table->string('approval_method',32);
	    $table->string('postsubmit_type',32);
	    $table->string('postsubmit_redirect_url',255);
	    $table->string('postsubmit_redirect_url_button_text',64);
	    $table->string('postsubmit_content_title',255);
	    $table->string('postsubmit_content_body',2048);
	    $table->string('postsubmit_content_image',255);
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
        Schema::dropIfExists('trigger_conf_landing_pages');
    }
}
