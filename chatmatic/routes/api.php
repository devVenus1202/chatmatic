<?php

/*
|--------------------------------------------------------------------------
| API Routes 
|--------------------------------------------------------------------------
*/

// Handle incoming stripe webhooks
Route::post('webhooks/stripe',   '\App\Http\Controllers\Stripe\WebhookController@incoming');

Route::namespace('API')->prefix('v1.2')->group(function(){

    // Login
    Route::post('/login',                                                        'AuthController@postLogin');

    // Public campaign (landing page)
    Route::get('/campaigns/{public_id}',                                        'PublicCampaignController@show');

    Route::post('/page/{page_uid}/integrations/{integration_uid}/trigger',      'IntegrationTriggerController@trigger');
    Route::get('/market_templates',                            'TemplateController@listMarket');
    Route::get('/templates/{template_uid}',                                     'TemplateController@template_preview');

    Route::middleware('APIToken')->group(function()
    {
        // Pusher/WebSocket Auth
        Route::post('/pusher/auth',                                             'PusherAuthenticationController@authenticate');

        // Integrations
        Route::get('/integrations/types',                                       'IntegrationTypeController@index');
        Route::get('/pages/{page_uid}/integrations',                             'IntegrationController@index');
        Route::post('/pages/{page_uid}/integrations',                            'IntegrationController@create');
        Route::put('/pages/{page_uid}/integrations/{integration_uid}',           'IntegrationController@update');
        Route::delete('/pages/{page_uid}/integrations/{integration_uid}',        'IntegrationController@delete');

        // Custom Fields
        Route::get('/pages/{page_uid}/custom-fields',                               'CustomFieldController@index');
        Route::post('/pages/{page_uid}/custom-fields',                              'CustomFieldController@create');
        Route::delete('/pages/{page_uid}/custom-fields/{custom_field_uid}',         'CustomFieldController@delete');
        Route::put('/pages/{page_uid}/custom-fields/{custom_field_uid}',            'CustomFieldController@update');
        Route::get('/pages/{page_uid}/custom-fields/{custom_field_uid}/subscribers','CustomFieldController@subscribers');

        // Logout
        Route::post('/logout',                                                  'AuthController@postLogout');
        // User
        Route::get('/userprofile',                                              'UserController@show');
        Route::post('/user/api/ext/token',                                      'UserController@createExtApiKey');
        Route::get('/user/sources',                                             'UserController@sources');
        // Pages
        Route::get('/pages',                                                    'PageController@index');
        Route::get('/pages/all',                                                'PageController@showAll');
        Route::post('/pages/all',                                               'PageController@connectAll');
        Route::patch('/pages/{page_uid}',                                       'PageController@update');
        // Home page data
        Route::get('/pages/{page_uid}/home',                                    'PageController@homeData');
        // Pages greeting
        Route::post('/pages/{page_uid}/greeting',                               'PageController@updateGreeting');
        // Campaigns
        Route::get('/pages/{page_uid}/campaigns',                               'CampaignController@index');
        Route::post('/pages/{page_uid}/campaigns',                              'CampaignController@create');
        Route::put('/pages/{page_uid}/campaigns/{campaign_uid}',                'CampaignController@update');
        Route::delete('/pages/{page_uid}/campaigns/{campaign_uid}',             'CampaignController@delete');
        // Workflow/Campaign Media
        Route::post('/pages/{page_uid}/fileupload',                             'WorkflowStepItemMediaController@create');
        Route::delete('/pages/{page_uid}/fileupload/{media_uid}',               'WorkflowStepItemMediaController@delete');
        // Workflows
        Route::get('/pages/{page_uid}/workflows',                               'WorkflowController@index');
        Route::post('/pages/{page_id}/workflows',                               'WorkflowController@create');
        Route::get('/pages/{page_id}/workflows/{workflow_id}',                  'WorkflowController@show');
        Route::patch('/pages/{page_id}/workflows/{workflow_id}',                'WorkflowController@update');
        Route::delete('/pages/{page_id}/workflows/{workflow_id}',               'WorkflowController@delete');
        Route::get('/pages/{page_id}/workflows/{workflow_id}/stats',            'WorkflowController@statistics');
        Route::patch('/pages/{page_id}/workflows/{workflow_id}/pict',           'WorkflowController@updatePicture');
        // Workflow Triggers
        Route::get('/pages/{page_uid}/workflow-triggers',                       'WorkflowTriggerController@index');
        Route::post('/pages/{page_uid}/workflow-triggers',                      'WorkflowTriggerController@create');        
        Route::patch('/pages/{page_uid}/workflow-triggers/{wt_uid}',            'WorkflowTriggerController@update');
        Route::delete('/pages/{page_uid}/workflow-triggers/{wt_uid}',           'WorkflowTriggerController@delete');
        // Broadcasts
        Route::get('/pages/{page_uid}/broadcasts',                              'BroadcastController@index');
        Route::post('/pages/{page_uid}/broadcasts',                             'BroadcastController@create');
        // Route::patch('/pages/{page_uid}/broadcasts/{broadcast_uid}',            'BroadcastController@update');
        Route::delete('/pages/{page_uid}/broadcasts/{broadcast_uid}',           'BroadcastController@delete');
        Route::post('/pages/{page_uid}/broadcasts/subscriber-count',            'BroadcastController@filterCount');
        Route::get('/pages/{page_uid}/broadcasts/{broadcast_uid}/fire',         'BroadcastController@fire_broadcast');
        // Workflow Steps
        Route::post('/pages/{page_uid}/favoriteStep',                           'WorkflowStepController@favorite');
        Route::delete('/pages/{page_uid}/favoriteStep',                         'WorkflowStepController@unFavorite');
        Route::post('/pages/{page_uid}/export_json',                            'WorkflowStepController@exportJson');
        // PersistentMenus
        Route::get('/pages/{page_uid}/menus',                                   'PersistentMenuController@index');
        Route::post('/pages/{page_uid}/menus',                                  'PersistentMenuController@create');
        Route::put('/pages/{page_uid}/menus',                                   'PersistentMenuController@toggleActive');
        Route::delete('/pages/{page_uid}/menus/{menu_uid}',                     'PersistentMenuController@delete');
        Route::put('/pages/{page_uid}/menus/{menu_uid}',                        'PersistentMenuController@update');
        // Automations
        Route::get('/pages/{page_uid}/automations',                             'AutomationController@index');
        Route::post('/pages/{page_uid}/automations',                            'AutomationController@create');
        Route::put('/pages/{page_uid}/automations/{automation_uid}',            'AutomationController@update');
        Route::delete('/pages/{page_uid}/automations/{automation_uid}',         'AutomationController@delete');
        // Subscribers
        Route::get('/pages/{page_uid}/subscribers',                             'SubscriberController@index');
        Route::get('/pages/{page_uid}/subscribers/export',                      'SubscriberController@export');
        Route::get('/pages/{page_uid}/subscribers/{subscriber_uid}',            'SubscriberController@show');
        Route::patch('/pages/{page_uid}/subscribers/{subscriber_uid}',          'SubscriberController@update');
        Route::get('/pages/{page_uid}/subscribers-history',                     'SubscriberCountHistoryController@index');
        Route::patch('/pages/{page_uid}/subscribers/{subscriber_uid}/live-chat','SubscriberController@toggleLiveChat');
        // Tags
        Route::get('/pages/{page_uid}/tags',                                    'TagController@index');
        Route::post('/pages/{page_uid}/tags',                                   'TagController@create');
        Route::delete('/pages/{page_uid}/tags/{tag_uid}',                       'TagController@delete');
        Route::get('/pages/{page_uid}/tags/{tag_uid}/subscribers',              'TagController@subscribers');
        // Templates
        Route::get('/pages/{page_uid}/templates',                               'TemplateController@index');
        Route::get('/pages/{page_uid}/templates/{template_uid}',                'TemplateController@show');
        Route::post('/pages/{page_uid}/template',                               'TemplateController@create');
        Route::post('/pages/{page_uid}/templates/import',                       'TemplateController@importWorkflow');
        Route::delete('/pages/{page_uid}/templates/{template_uid}',             'TemplateController@delete');
        Route::patch('/pages/{page_uid}/templates/{template_uid}',              'TemplateController@update');
        Route::post('/pages/{page_uid}/templates/{template_uid}/buy',           'TemplateController@buy');
        Route::post('/pages/{page_uid}/templates/{template_uid}/redem_sumo',     'TemplateController@redem_sumo');
        // Triggers
        //Route::post('/pages/{page_uid}/triggers',                               'TriggerController@create');
        //Route::put('/pages/{page_uid}/triggers/{trigger_id}',                   'TriggerController@update');
        // Posts
        Route::get('/pages/{page_uid}/posts',                                   'PostController@index');
        // Admins
        Route::get('/pages/{page_uid}/admins',                                  'PageAdminController@index');
        Route::post('/pages/{page_uid}/admins',                                 'PageAdminController@create');
        Route::delete('/pages/{page_uid}/admins/{admin_uid}',                   'PageAdminController@delete');
        // Licences
        Route::post('/pages/{page_uid}/license',                                'LicenseController@create');
        Route::get('/pages/{page_uid}/billing-info',                            'LicenseController@billingInfo');
        Route::patch('/pages/{page_uid}/license',                               'LicenseController@update');
        Route::delete('/pages/{page_uid}/license',                              'LicenseController@delete');
        // Licenses sumo app
        Route::post('/pages/{page_uid}/appsumo_license',                        'LicenseController@license_page');
        Route::get('/appsumo_info',                                             'LicenseController@appsumo_license_info');

        // Coupons
        Route::post('/pages/{page_uid}/coupon-check',                           'CouponController@check');
        // Domains - WhitesList
        Route::get('pages/{page_uid}/domains',                                  'DomainController@index');
        Route::post('pages/{page_uid}/domains',                                 'DomainController@update');

        // Sms
        Route::get('pages/{page_uid}/sms',                                       'SmsController@index');
        Route::get('pages/{page_uid}/sms/activate_free_trial',                  'SmsController@activate_free_trial');
        Route::post('pages/{page_uid}/sms/purchase',                             'SmsController@purchase_plan');

        // 
        Route::get('/get-profile/{user_id}',                                    'UserController@getProfile');
        Route::post('/set-user-info/{user_id}',                                 'UserController@saveUserInfo');
        Route::get('/get-follow-info/{user_id}',                                'UserController@getFollowInfo');
        Route::post('/follow-user',                                             'UserController@followUser');
        Route::get('/get-template-info/{user_id}',                              'UserController@getTemplateInfo');
        Route::get('/get-sales-info/{user_id}',                                 'UserController@getSalesInfo');
    });
});

Route::namespace('API\Zapier')->prefix('zapier/v1')->group(function()
{
    // Triggers coming from pipeline
    Route::post('new-subscriber',       'InternalController@newSubscriber');
    Route::post('new-tag',              'InternalController@newTag');
    Route::post('phone-updated',        'InternalController@updatedPhone');
    Route::post('email-updated',        'InternalController@updatedEmail');
    Route::post('attribute-updated',    'InternalController@updatedAttribute');

    // Subscriptions/Individual Zaps and their respective webhook subscriptions
    Route::post('subscription',         'SubscriptionController@create');
    Route::delete('subscription',       'SubscriptionController@destroy');
    Route::get('subscription',          'SubscriptionController@list');

    // Dynamic dropdown triggers (hidden)
    Route::get('pages',                 'UserController@allPages');
    Route::get('tags',                  'UserController@allTags');
    Route::get('custom-fields',         'UserController@allCustomFields');
    Route::get('workflows',             'UserController@allWorkflows');

    // Actions
    Route::post('tag-subscriber',       'UserController@tagSubscriber');
    Route::post('update-custom-field',  'UserController@updateSubscriberCustomField');
    Route::post('find-subscriber',      'UserController@findSubscriber');
    Route::post('send-message',         'UserController@sendMessage');

    // Auth
    Route::get('auth',                  'AuthController@index');

    // Autorenew that comes from pipeline
    Route::post('autorenew-sms',        'InternalController@renovateSms');
});


Route::namespace('API\AppSumo')->prefix('appsumo/v1')->group(function()
{

    Route::post('auth',      'AuthController@index');

    // Authenticated users
    Route::post('notification',         'NotificationController@index')->middleware('AppSumo');
 
});