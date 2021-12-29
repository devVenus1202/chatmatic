<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddArchivedFieldsToWorkflowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->boolean('archived')->after('name')->nullable();
            $table->timestamp('archived_at_utc', 6)->after('updated_at_utc')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->dropColumn('archived');
            $table->dropColumn('archived_at_utc');
        });
    }
}
