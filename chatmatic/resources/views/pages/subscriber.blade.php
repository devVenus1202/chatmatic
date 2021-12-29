@extends('layouts.app')

@section('title', 'Post Detail - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/pages">Pages</a></li>
                    <li class="breadcrumb-item"><a href="/page/{!! $page->uid !!}">{!! $page->fb_name !!}</a></li>
                    <li class="breadcrumb-item"><a href="/page/{!! $page->uid !!}/subscribers">Subscribers</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Subscriber Detail</li>
                </ol>
            </nav>
            <h3>Subscriber Detail</h3>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    Subscriber Details
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                        <tr>
                            <td><strong>UID</strong></td>
                            <td class="text-right">{!! $subscriber->uid !!}</td>
                        </tr>
                        <tr>
                            <td><strong>User PSID</strong></td>
                            <td class="text-right">{!! $subscriber->user_psid !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Name</strong></td>
                            <td class="text-right">{!! $subscriber->first_name !!} {!! $subscriber->last_name !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Email</strong></td>
                            <td class="text-right">{!! $subscriber->email !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Phone</strong></td>
                            <td class="text-right">{!! $subscriber->phone_number !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Locale</strong></td>
                            <td class="text-right">{!! $subscriber->locale !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Timezone</strong></td>
                            <td class="text-right">{!! $subscriber->timezone !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Gender</strong></td>
                            <td class="text-right">{!! $subscriber->gender !!}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    Subscriber Interaction Snapshot
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                        <tr>
                            <td><strong>Last Engagement</strong></td>
                            <td class="text-right">{!! $subscriber->last_engagement_utc !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Last Read Watermark</strong></td>
                            <td class="text-right">{!! $subscriber->last_read_watermark !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Pause Subscriptions Until</strong></td>
                            <td class="text-right">{!! $subscriber->pause_subscriptions_until_utc !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Messages Attempted from Bot</strong></td>
                            <td class="text-right">{!! $subscriber->messages_attempted_from_bot !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Messages Accepted from Bot</strong></td>
                            <td class="text-right">{!! $subscriber->messages_accepted_from_bot !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Messages Attempted from Page</strong></td>
                            <td class="text-right">{!! $subscriber->messages_attempted_from_page !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Messages Accepted from Page</strong></td>
                            <td class="text-right">{!! $subscriber->messages_accepted_from_page !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Messages Read</strong></td>
                            <td class="text-right">{!! $subscriber->messages_read !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Total Clicks</strong></td>
                            <td class="text-right">{!! $subscriber->total_clicks !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Tags</strong></td>
                            <td class="text-right">
                                @foreach($subscriber->tags as $tag)
                                    {!! $tag->value !!}<br>
                                @endforeach
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Custom Fields</strong></td>
                            <td class="text-right">
                                @foreach($subscriber->customFieldResponses as $response)
                                    <strong>{!! $response->customField->field_name !!}</strong>: {!! $response->response !!}<br>
                                @endforeach
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if($subscriber->chatHistory()->count())

    <div class="row pt-4">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    Message History
                </div>
                <div class="card-body">
                    <div class="alert alert-info">Removed, for now. (It wasn't accurate, will replace with what we use for live chat)</div>
                </div>
            </div>
        </div>
    </div>
    @endif

@endsection
