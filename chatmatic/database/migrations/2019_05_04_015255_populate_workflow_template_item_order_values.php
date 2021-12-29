<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PopulateWorkflowTemplateItemOrderValues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // First we'll grab all the workflow steps
        $workflow_steps = \App\WorkflowTemplateStep::all();

        // Now we'll loop through them all to order their step items
        foreach($workflow_steps as $workflow_step)
        {
            $order = 0;
            foreach($workflow_step->workflowTemplateStepItems()->orderBy('uid')->get() as $workflow_step_item)
            {
                DB::table('workflow_template_step_items')->where('uid', $workflow_step_item->uid)->update(['item_order' => $order]);

                $order++;
            }
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
