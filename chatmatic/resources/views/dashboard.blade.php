@extends('layouts.app')

@section('title', 'Dashboard - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <h3>Admin Dashboard</h3>
        </div>
        <div class="col text-right">
            @php $sys_load = sys_getloadavg(); @endphp

            <table class="">
                <tbody>
                <tr>
                    <td>System Load:</td>
                    <td class="text-center" style="padding: 0 3px 0 3px;">
                        <div class="" style="padding: 2px 5px 2px 5px;">
                            {!! number_format($sys_load[0], 2) !!}
                        </div>
                        <small>1min</small>
                    </td>
                    <td class="text-center" style="padding: 0 3px 0 3px;">
                        <div class="" style="padding: 2px 5px 2px 5px;">
                            {!! number_format($sys_load[1], 2) !!}
                        </div>
                        <small>5min</small>
                    </td>
                    <td class="text-center" style="padding: 0 3px 0 3px;">
                        <div class="" style="padding: 2px 5px 2px 5px;">
                            {!! number_format($sys_load[2], 2) !!}
                        </div>
                        <small>15min</small>
                    </td>
                </tr>
                </tbody>
            </table>

        </div>
        <div class="col text-right">
            <a href="/horizon" target="_blank" class="btn btn-sm btn-primary">Horizon (PHP Queue) &nbsp;<i class="fa fa-external-link" aria-hidden="true"></i></a>
            <a href="/pipeline" target="_blank" class="btn btn-sm btn-primary">Pipeline &nbsp;<i class="fa fa-external-link" aria-hidden="true"></i></a>
        </div>
    </div>

    <div class="row">
        <div class="col-5">
            <div class="card">
                <div class="card-header">
                    Platform Snapshot
                </div>
                <div class="card-body">

                    <div class="row">
                        <div class="col text-right">
                            <form action="/users" class="form-inline float-right">
                                <label class="sr-only" for="search_value">User Search</label>
                                <input type="text" class="form-control mb-2 mr-sm-2" id="search_value" name="search" placeholder="User Search...">
                                <button type="submit" class="btn btn-primary mb-2">Search</button>
                            </form>
                        </div>
                    </div>

                    <table class="table table-sm">
                        <tbody>
                        <tr>
                            <td><strong>Users</strong></td>
                            <td class="text-right"><a href="/users">{!! number_format($user_count) !!}</a></td>
                        </tr>
                        <tr>
                            <td>Users added in the last 24 hours</td>
                            <td class="text-right">{!! number_format($new_user_count) !!}</td>
                        </tr>
                        <tr>
                            <td>Users added in the last 7 days</td>
                            <td class="text-right">{!! number_format($week_new_user_count) !!}</td>
                        </tr>
                        <tr>
                            <td>Referral Users</td>
                            <td class="text-right"><a href="/users/referred">{!! number_format($referral_user_count) !!}</a></td>
                        </tr>
                        </tbody>
                    </table>

                    <div class="row">
                        <div class="col text-right">
                            <form action="/pages" class="form-inline float-right">
                                <label class="sr-only" for="search_value">Name</label>
                                <input type="text" class="form-control mb-2 mr-sm-2" id="search_value" name="search" placeholder="Page Search...">
                                <button type="submit" class="btn btn-primary mb-2">Search</button>
                            </form>
                        </div>
                    </div>

                    <table class="table table-sm">
                        <tbody>
                        <tr>
                            <td><strong>Pages</strong></td>
                            <td class="text-right"><a href="/pages">{!! number_format($page_count) !!}</a></td>
                        </tr>
                        <tr>
                            <td>Connected Pages</td>
                            <td class="text-right">{!! number_format($connected_page_count) !!}</td>
                        </tr>
                        <tr>
                            <td>Average Connected Pages per User</td>
                            <td class="text-right">{!! round(($connected_page_count / $user_count), 1) !!}</td>
                        </tr>
                        <tr>
                            <td>Avg Subs per Connected Page</td>
                            <td class="text-right">{!! round($subscriber_count / $connected_page_count) !!}</td>
                        </tr>
                        <tr>
                            <td>Connected Pages w/ 250+ Subs</td>
                            <td class="text-right"><a href="/pages/connected-250">{!! number_format(\App\Page::where('is_connected', 1)->where('subscribers', '>=', 250)->count()) !!}</a></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-7">
            <div class="card">
                <div class="card-header">
                    Counters / View Records
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <table class="table table-sm">
                                <tbody>
                                <tr>
                                    <td><strong>Automations</strong></td>
                                    <td class="text-right">{!! number_format(\App\Automation::count()) !!}</td>
                                </tr>
                                <tr>
                                    <td><strong>Comments</strong></td>
                                    <td class="text-right">{!! number_format(\App\Comment::count()) !!}</td>
                                </tr>
                                <tr>
                                    <td><strong>Comment Triggers</strong></td>
                                    <td class="text-right">{!! number_format(\App\Trigger::count()) !!}</td>
                                </tr>
                                <tr>
                                    <td><strong>Custom Fields</strong></td>
                                    <td class="text-right">{!! number_format(\App\CustomField::count()) !!}</td>
                                </tr>
                                <tr>
                                    <td><strong>Interactions</strong></td>
                                    <td class="text-right">{!! number_format(\App\Interaction::count()) !!}</td>
                                </tr>
                                <tr>
                                    <td><strong>Integrations</strong></td>
                                    <td class="text-right">{!! number_format(\App\Integration::count()) !!}</td>
                                </tr>
                                <tr>
                                    <td><strong>Zapier Triggers</strong></td>
                                    <td class="text-right"><a href="zaps">{!! number_format(\App\ZapierWebhookSubscription::count()) !!}</a></td>
                                </tr>
                                <tr>
                                    <td><strong>Zapier Events</strong></td>
                                    <td class="text-right"><a href="zapier/events">{!! number_format(\App\ZapierEventLog::count()) !!}</a></td>
                                </tr>
                                <tr>
                                    <td><strong>Outbound Links</strong></td>
                                    <td class="text-right">{!! number_format(\App\OutboundLink::count()) !!}</td>
                                </tr>
                                <tr>
                                    <td><strong>Updates</strong></td>
                                    <td class="text-right">
                                        <a href="feed_updates">
                                            {!! number_format(\App\ChatmaticFeedUpdate::count()) !!}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Sms accounts</strong></td>
                                    <td class="text-right">
                                        <a href="sms_accounts">
                                            {!! number_format(\App\SmsBalance::count()) !!}
                                        </a>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col">
                            <table class="table table-sm">
                                <tbody>
                                <tr>
                                    <td><strong>Page Admins</strong></td>
                                    <td class="text-right">{!! number_format(\App\PageAdmin::count()) !!}</td>
                                </tr>
                                <tr>
                                    <td><strong>Posts</strong></td>
                                    <td class="text-right">{!! number_format(\App\Post::count()) !!}</td>
                                </tr>
                                <tr>
                                    <td><strong>Stripe Subscriptions</strong></td>
                                    <td class="text-right"><a href="subscriptions">{!! number_format($stripe_subscription_count) !!}</a></td>
                                </tr>
                                <tr>
                                    <td><strong>Subscriber Notes</strong></td>
                                    <td class="text-right">{!! number_format(\App\SubscriberNote::count()) !!}</td>
                                </tr>
                                <tr>
                                    <td><strong>Subscribers</strong></td>
                                    <td class="text-right">{!! number_format($subscriber_count) !!}</td>
                                </tr>
                                <tr>
                                    <td><strong>Templates</strong></td>
                                    <td class="text-right">
                                        <a href="/templates">{!! number_format(\App\WorkflowTemplate::count()) !!}</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Tags (applied)</strong></td>
                                    <td class="text-right">{!! number_format(\App\Tag::count()) !!} ({!! number_format(\DB::table('taggables')->count()) !!})</td>
                                </tr>
                                <tr>
                                    <td><strong>View Pipeline</strong></td>
                                    <td class="text-right"><a href="/pipeline">View</a></td>
                                </tr>
                                <tr>
                                    <td><strong>Workflows</strong></td>
                                    <td class="text-right">{!! number_format(\App\Workflow::count()) !!}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tips</strong></td>
                                    <td class="text-right">
                                        <a href="feed_tips">
                                            {!! number_format(\App\ChatmaticFeedTip::count()) !!}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>AppSumo Users</strong></td>
                                    <td class="text-right">
                                        <a href="appsumo_users">
                                            {!! number_format(\App\AppSumoUser::count()) !!}
                                        </a>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <br>

    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    Recently added users
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                        <th>Email / Name</th>
                        <th class="text-center">Pages (connected)</th>
                        <th class="text-center">Subscribers</th>
                        <th class="text-right">Created</th>
                        </thead>
                        <tbody>
                        @foreach($recently_added_users as $recent_user)
                            @php
                                $subscribers_count = 0;
                                foreach($recent_user->pages()->get() as $recent_user_page)
                                {
                                    $subscribers_count = $subscribers_count + $recent_user_page->subscribers()->count();
                                }
                            @endphp
                            <tr>
                                <td>
                                    <a href="/user/{!! $recent_user->uid !!}">{!! $recent_user->facebook_email !!}</a>
                                    <br>
                                    <a href="/user/{!! $recent_user->uid !!}">{!! $recent_user->facebook_name !!}</a>
                                </td>
                                <td class="text-center">{!! $recent_user->pages()->count() !!} ({!! $recent_user->pages()->where('is_connected', 1)->count() !!})</td>
                                <td class="text-center">
                                    @if($subscribers_count > 0)
                                        {!! number_format($subscribers_count) !!}
                                    @endif
                                </td>
                                <td class="text-right">{!! $recent_user->created_at_utc->diffForHumans() !!}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-header">
                    Recently added users with subscribers
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                        <th>Email / Name</th>
                        <th class="text-center">Pages (connected)</th>
                        <th class="text-center">Subscribers</th>
                        <th class="text-right">Created</th>
                        </thead>
                        <tbody>
                        @php
                            $users_shown = 0;
                        @endphp
                        @foreach($recently_added_users_ext as $recent_user)
                            @if($users_shown < 11)
                                @php
                                    $subscribers_count = 0;
                                    foreach($recent_user->pages()->get() as $recent_user_page)
                                    {
                                        $subscribers_count = $subscribers_count + $recent_user_page->subscribers()->count();
                                    }
                                    if($subscribers_count > 0)
                                        $users_shown++;
                                @endphp
                                @if($subscribers_count > 0)
                                    <tr>
                                        <td>
                                            <a href="/user/{!! $recent_user->uid !!}">{!! $recent_user->facebook_email !!}</a>
                                            <br>
                                            <a href="/user/{!! $recent_user->uid !!}">{!! $recent_user->facebook_name !!}</a>
                                        </td>
                                        <td class="text-center">{!! $recent_user->pages()->count() !!} ({!! $recent_user->pages()->where('is_connected', 1)->count() !!})</td>
                                        <td class="text-center">
                                            {!! number_format($subscribers_count) !!}
                                        </td>
                                        <td class="text-right">{!! $recent_user->created_at_utc->diffForHumans() !!}</td>
                                    </tr>
                                @endif
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection
