<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateArchivedValuesOnWorkflowsWhereNull extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $workflows = \App\Workflow::whereNull('archived')->get();

        foreach($workflows as $workflow)
        {
            $workflow->archived = 0;
            $workflow->save();
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
