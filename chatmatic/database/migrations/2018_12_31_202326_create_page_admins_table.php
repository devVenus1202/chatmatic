<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePageAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('page_admins', function (Blueprint $table) {
            $table->increments('uid');
            $table->bigInteger('page_uid')->index();
            $table->bigInteger('user_uid')->index();
            $table->bigInteger('added_by')->index();
            $table->boolean('deleted')->default(0);
            $table->timestamp('created_at_utc');
            $table->timestamp('updated_at_utc');
        });
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
