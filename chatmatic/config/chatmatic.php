<?php

return [

    'max_free_subscribers'          => 250,
    'autoresponse_delay'            => 24, // Delay in hours between autoresponses being sent to users
    'app_id'                        => getenv('FACEBOOK_APP_ID'),
    'app_secret'                    => getenv('FACEBOOK_APP_SECRET'),
    'verify_token'                  => getenv('FACEBOOK_VERIFY_TOKEN'),
    'image_hosting_path'            => getenv('IMAGE_HOSTING_PATH'),
    'pipeline_internal_base_url'    => getenv('PIPELINE_INTERNAL_BASE_URL'),

    'services'              => [
        'stripe' => [
            'secret' => getenv('STRIPE_SECRET')
        ]
    ]
];
