<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSellingFieldsToWorkflowTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workflow_templates', function (Blueprint $table) {
		$table->string('description',500)->nullable();
		$table->decimal('price')->nullable();
		$table->string('video_url',255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('workflow_templates', function (Blueprint $table) {
            //
        });
    }
}
