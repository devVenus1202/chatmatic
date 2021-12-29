<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route to handle outbound links
Route::get('o/{slug}/{trigger_uid}/{button_uid}/{subscriber_uid}', 'OutboundLinkController@show');

Route::group(['middleware' => 'auth.very_basic'], function(){

    Route::get('/',                                     'HomeController@index');
    Route::get('users',                                 'UsersController@index');
    Route::get('users/most-pages',                      'UsersController@mostPages');
    Route::get('users/most-connected-pages',            'UsersController@mostConnectedPages');
    Route::get('user/{user_id}',                        'UsersController@show');
    Route::get('user/{user_id}/licensing',              'UsersController@licensing');
    Route::get('user/{user_id}/sessions',               'UsersController@sessions');
    Route::get('users/referred',                        'UsersController@referred');

    Route::get('appsumo_users',                         'UsersController@appsumo');
    Route::get('appsumo_user/{sumo_id}',                'UsersController@appsumoLogin');

    Route::get('zaps',                                  'ZapsController@index');
    Route::get('zap/{zap_uid}',                         'ZapsController@show');

    Route::get('zapier/events',                         'ZapierEventLogController@index');
    Route::get('zapier/events/{event_uid}',             'ZapierEventLogController@show');

    Route::get('page/{page_id}/zapier/events',          'PagesController@showZapierEvents');
    Route::get('page/{page_id}/zapier/events/{event_uid}','PagesController@showZapierEvent');

    Route::get('broadcasts',                            'BroadcastsController@index');

    Route::get('pages',                                 'PagesController@index');
    Route::get('pages/connected-250',                   'PagesController@connected250');
    Route::get('page/{page_id}',                        'PagesController@show');

    Route::get('page/{page_id}/custom-fields',                      'PagesController@showCustomFields');
    Route::get('page/{page_id}/custom-field/{custom_field_uid}',    'PagesController@showCustomField');

    Route::get('page/{page_id}/broadcasts',                             'PagesController@showBroadcasts');
    Route::get('page/{page_id}/broadcast/{broadcast_uid}/messages',    'PagesController@showBroadcastMessages');

    Route::get('page/{page_id}/flow_triggers',                          'PagesController@showWorkflowTriggers');
    Route::get('page/{page_id}/flow_trigger/{flow_triggers_id}',       'PagesController@showWorkflowTrigger');

    Route::get('page/{page_id}/comments',               'PagesController@showComments');

    Route::get('page/{page_id}/workflows',              'PagesController@showWorkflows');
    Route::get('page/{page_id}/workflow/{workflow_id}', 'PagesController@showWorkflow');


    Route::get('page/{page_id}/triggers',               'PagesController@showTriggers');
    Route::get('page/{page_id}/trigger/{trigger_id}',   'PagesController@showTrigger');

    Route::get('page/{page_id}/posts',                  'PagesController@showPosts');
    Route::get('page/{page_id}/post/{post_id}',         'PagesController@showPost');

    Route::get('page/{page_id}/subscribers',            'PagesController@showSubscribers');
    Route::get('page/{page_id}/subscriber/{sub_id}',    'PagesController@showSubscriber');
    Route::post('page/{page_id}/subscribers/import',    'PagesController@importPSIDs');

    Route::get('page/{page_id}/disconnect',             'PagesController@disconnect');

    Route::get('page/{page_id}/workflow/{workflow_id}/delete',              'PagesController@deleteWorkflow');
    Route::get('page/{page_id}/workflow_trigger/{workflow_id}/archive',     'PagesController@archiveWorkflow');
    Route::get('page/{page_id}/workflow_trigger/{workflow_id}/un-archive',  'PagesController@unArchiveWorkflow');
    Route::post('page/{page_id}/workflow/{workflow_id}/create-template',    'PagesController@createTemplate');

    Route::get('templates',                                     'TemplatesController@index');
    Route::get('templates/market',                              'TemplatesController@market');
    Route::get('templates/on_market',                           'TemplatesController@onMarket');
    Route::get('template/{template_id}',                        'TemplatesController@show')->name('template-show');
    Route::get('template/{template_id}/update',                 'TemplatesController@edit');
    Route::post('template/{template_id}/update',                'TemplatesController@update');
    Route::get('template/{template_id}/publish',                'TemplatesController@publish');
    Route::get('template/{template_id}/archive',                'TemplatesController@archive');
    Route::post('template/{template_id}/push-to-page',          'TemplatesController@pushToPage');
    Route::get('template/{template_id}/sold',                   'TemplatesController@sold');

    Route::get('subscriptions',                         'StripeSubscriptionsController@index');
    Route::get('subscriptions/{sub_id}/delete',         'StripeSubscriptionsController@delete');

    Route::get('pipeline',                              'PipelineController@index');

    Route::get('feed_updates',                          'FeedUpdateController@index');
    Route::get('feed_updates/new',                      'FeedUpdateController@create');
    Route::post('feed_updates/new',                     'FeedUpdateController@save');
    Route::get('feed_updates/{update_id}/update',       'FeedUpdateController@edit');
    Route::post('feed_updates/{update_id}/update',      'FeedUpdateController@update');
    Route::get('feed_updates/{update_id}/delete',       'FeedUpdateController@delete');

    Route::get('feed_tips',                          'FeedTipController@index');
    Route::get('feed_tips/new',                      'FeedTipController@create');
    Route::post('feed_tips/new',                     'FeedTipController@save');
    Route::get('feed_tips/{tip_id}/update',          'FeedTipController@edit');
    Route::post('feed_tips/{tip_id}/update',         'FeedTipController@update');
    Route::get('feed_tips/{tip_id}/delete',          'FeedTipController@delete');

    Route::get('sms_accounts',                       'SmsController@index');

    Route::get('database/clean', function()
    {
        if(App::environment() !== 'local')
            die('nope');
        echo 'Clean it up'.'<br />';

        $mikes_id = 449;
        $page_ids = [11069,15896,23142];

        DB::table('auth_ticket')->whereNotIn('chatmatic_user_uid', [$mikes_id])->delete();
        DB::table('broadcasts')->whereNotIn('page_uid', $page_ids)->delete();
        DB::table('campaigns')->whereNotIn('page_uid', $page_ids)->delete();
        DB::table('chatmatic_page_licenses')->whereNotIn('page_uid', $page_ids)->delete();
        DB::table('chatmatic_user_page_map')->whereNotIn('chatmatic_user_uid', [$mikes_id])->delete();
        DB::table('chatmatic_user_stripe_coupon_usages')->whereNotIn('chatmatic_user_uid', [$mikes_id])->delete();
        DB::table('chatmatic_users')->whereNotIn('uid', [$mikes_id])->delete();
        DB::table('comments')->whereNotIn('page_uid', $page_ids)->delete();
        DB::table('pages')->whereNotIn('uid', $page_ids)->delete();
        DB::table('posts')->whereNotIn('page_uid', $page_ids)->delete();
        DB::table('stripe_subscriptions')->whereNotIn('chatmatic_user_uid', [$mikes_id])->delete();
        DB::table('subscriber_chat_history')->whereNotIn('page_uid', $page_ids)->delete();
        DB::table('subscriber_count_history')->whereNotIn('page_uid', $page_ids)->delete();
        DB::table('subscriber_delivery_history')->whereNotIn('page_uid', $page_ids)->delete();
        DB::table('subscriber_notes')->whereNotIn('page_uid', $page_ids)->delete();
        DB::table('subscribers')->whereNotIn('page_uid', $page_ids)->delete();
        DB::table('subscriptions')->whereNotIn('page_uid', $page_ids)->delete();
        DB::table('triggers')->whereNotIn('page_uid', $page_ids)->delete();
        DB::table('workflow_step_item_images')->whereNotIn('page_uid', $page_ids)->delete();
        DB::table('workflow_step_item_map')->whereNotIn('page_uid', $page_ids)->delete();
        DB::table('workflow_step_items')->whereNotIn('page_uid', $page_ids)->delete();
        DB::table('workflow_steps')->whereNotIn('page_uid', $page_ids)->delete();
        DB::table('workflows')->whereNotIn('page_uid', $page_ids)->delete();
    });
});
