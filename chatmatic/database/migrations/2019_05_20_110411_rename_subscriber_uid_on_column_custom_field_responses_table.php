<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameSubscriberUidOnColumnCustomFieldResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('custom_field_responses', function (Blueprint $table) {
            $table->renameColumn('subscriber_uid', 'subscriber_psid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('custom_field_responses', function (Blueprint $table) {
            //
        });
    }
}
