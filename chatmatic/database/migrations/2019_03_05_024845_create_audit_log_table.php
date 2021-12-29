<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuditLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audit_log', function (Blueprint $table) {
            $table->increments('uid');
            $table->bigInteger('chatmatic_user_uid')->nullable()->index();
            $table->bigInteger('page_uid')->nullable()->index();
            $table->string('event', 48)->index();
            $table->string('message');
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
