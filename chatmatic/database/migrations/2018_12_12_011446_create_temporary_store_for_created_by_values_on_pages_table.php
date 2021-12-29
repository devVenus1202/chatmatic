<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTemporaryStoreForCreatedByValuesOnPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create the new field as a big integer
        Schema::table('pages', function (Blueprint $table) {

            $table->bigInteger('created_by_new')->nullable();

        });

        DB::statement('update pages set created_by_new = CAST(created_by AS integer);');

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
