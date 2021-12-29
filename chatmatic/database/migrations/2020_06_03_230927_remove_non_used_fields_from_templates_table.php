<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveNonUsedFieldsFromTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workflow_templates', function (Blueprint $table) {
            $table->dropColumn(['workflow_type', 'keywords','keywords_option']);
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
            $table->string('workflow_type');
            $table->string('keywords');
            $table->string('keywords_option');
        });
    }
}
