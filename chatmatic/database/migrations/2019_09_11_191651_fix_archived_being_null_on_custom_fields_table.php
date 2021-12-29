<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixArchivedBeingNullOnCustomFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $custom_fields = \App\CustomField::whereNull('archived')->get();

        foreach($custom_fields as $custom_field)
        {
            $custom_field->archived = false;
            $custom_field->save();
        }
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
