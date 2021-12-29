@extends('layouts.app')

@section('title', 'Page Detail - Chatmatic Admin')

@section('content')
    <div class="row pt-4 pb-2">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/pages">Pages</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{!! $page->fb_name !!}</li>
                </ol>
            </nav>
            <h3>{!! $page->fb_name !!}</h3>
            <h4><small><a href="{!! $page->fb_link !!}" target="_blank">{!! $page->fb_link !!}</a></small></h4>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    Page Details
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                        <tr>
                            <td><strong>Chatmatic Page ID</strong></td>
                            <td class="text-right">{!! $page->uid !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Facebook Page ID</strong></td>
                            <td class="text-right">{!! $page->fb_id !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Licensed</strong></td>
                            <td class="text-right">
                                @if($page->licenses()->count() > 0)
                                    <span class="badge badge-success">Yes</span>
                                @elseif($page->subscribers()->count() > (config('chatmatic.max_free_subscribers') - 1))
                                    <span class="badge badge-danger">No</span>
                                @else
                                    <span class="badge badge-warning">No</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Name</strong></td>
                            <td class="text-right">{!! $page->fb_name !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Category</strong></td>
                            <td class="text-right">{!! $page->fb_category !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Added by</strong></td>
                            <td class="text-right"><a href="/user/{!! $page->user->uid !!}">{!! $page->user->facebook_name !!}</a></td>
                        </tr>
                        <tr>
                            <td><strong>Connected</strong></td>
                            <td class="text-right">
                                @if($page->is_connected)
                                    Yes <a class="btn btn-sm btn-danger" href="/page/{!! $page->uid !!}/disconnect">Disconnect</a>
                                @else No @endif</td>
                        </tr>
                        <tr>
                            <td><strong>Created</strong></td>
                            <td class="text-right">{!! $page->created_at_utc->format("m/d/Y") !!} / {!! $page->created_at_utc->diffForHumans() !!}</td>
                        </tr>
                        @if($page->twilio_number && $page->sms_balance)
                        <tr>
                            <td><strong>Sms Phone Number</strong></td>
                            <td class="text-right">{!! $page->twilio_number !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Sms Balance</strong></td>
                            <td class="text-right">{!! $page->sms_balance->total !!}</td>
                        </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    Relational Object Counts
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                        <tr>
                            <td><strong>Subscribers</strong></td>
                            <td class="text-right"><a href="/page/{!! $page->uid !!}/subscribers">{!! number_format($page->subscribers()->count()) !!}</a></td>
                        </tr>
                        <tr>
                            <td><strong>Flows</strong></td>
                            <td class="text-right"><a href="/page/{!! $page->uid !!}/workflows">{!! number_format($page->workflows()->count()) !!}</a></td>
                        </tr>
                        <tr>
                            <td><strong>Flow Triggers</strong></td>
                            <td class="text-right"><a href="/page/{!! $page->uid !!}/flow_triggers">{!! number_format( $page->workflowTriggers->count() ) !!}</a></td>
                        </tr>
                        <tr>
                            <td><strong>Integrations</strong></td>
                            <td class="text-right">{!! number_format($page->integrations()->count()) !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Integrations Sent</strong></td>
                            <td class="text-right">{!! number_format($page->integrationRecords()->count()) !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Posts</strong></td>
                            <td class="text-right"><a href="/page/{!! $page->uid !!}/posts">{!! number_format($page->posts()->count()) !!}</a></td>
                        </tr>
                        <tr>
                            <td><strong>Comments</strong></td>
                            <td class="text-right"><a href="/page/{!! $page->uid !!}/comments">{!! number_format($page->comments()->count()) !!}</a></td>
                        </tr>
                        <tr>
                            <td><strong>Subscriptions</strong></td>
                            <td class="text-right">{!! number_format($page->subscriptions()->count()) !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Custom Fields</strong></td>
                            <td class="text-right"><a href="/page/{!! $page->uid !!}/custom-fields">{!! number_format($page->customFields()->count()) !!}</a></td>
                        </tr>
                        <tr>
                            <td><strong>Zapier Trigger Subscriptions</strong></td>
                            <td class="text-right">{!! number_format($page->zapierWebhookSubscriptions()->count()) !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Zapier Events</strong></td>
                            <td class="text-right"><a href="/page/{!! $page->uid !!}/zapier/events">{!! number_format($page->zapierEventLogs()->count()) !!}</a></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @php $api_errors = $page->apiErrors()->orderBy('uid', 'desc')->take(20)->get(); @endphp
    @if($api_errors->count())
        <br>

        <div class="row">
            <div class="col">
                @foreach($api_errors as $api_error)
                    <div class="alert alert-warning" role="alert">
                        <div class="row">
                            <div class="col-9">
                                {!! $api_error->error_msg !!}
                            </div>
                            <div class="col-3 text-right">
                                {!! $api_error->created_at_utc->diffForHumans() !!}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <br>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-warning" role="alert" style="overflow: auto">
                Token: {!! $page->facebook_connected_access_token !!}
            </div>
        </div>
    </div>
@endsection