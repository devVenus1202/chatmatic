<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PopulateIntegrationTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $integration_type = [
            'name'          => 'Webhook',
            'slug'          => str_slug('Webhook'),
            'parameters'    => json_encode([
                'webhook_url'
            ])
        ];

        $integration_type = \App\IntegrationType::create($integration_type);
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
