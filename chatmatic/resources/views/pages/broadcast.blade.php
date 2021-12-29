@extends('layouts.app')

@section('title', 'Broadcast Details - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/pages">Pages</a></li>
                    <li class="breadcrumb-item"><a href="/page/{!! $page->uid !!}">{!! $page->fb_name !!}</a></li>
                    <li class="breadcrumb-item"><a href="/page/{!! $page->uid !!}/broadcasts">Broadcasts</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Broadcast Details</li>
                </ol>
            </nav>
            <h3>Broadcast Details</h3>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="card">
                <div class="card-header">
                    Broadcast Details
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                        <tr>
                            <td><strong>Workflow</strong></td>
                            <td class="text-right">
                                <a href="/page/{!! $broadcast->workflowTrigger->page->uid !!}/workflow/{!! $broadcast->workflow_trigger_uid !!}">
                                    {!! $broadcast->workflowTrigger->name !!}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Status</strong></td>
                            <td class="text-right">
                                {!! $broadcast->statusString() !!}
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Messages Sent</strong></td>
                            <td class="text-right">{!! number_format($broadcast->interactions->count()) !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Created</strong></td>
                            <td class="text-right">
                                {!! $broadcast->workflowTrigger->created_at_utc->diffForHumans() !!} <br>
                                {!! $broadcast->workflowTrigger->created_at_utc !!}
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Fire At (If Scheduled)</strong></td>
                            <td class="text-right">
                                @if($broadcast->fire_at_utc !== null)
                                    {!! \Carbon\Carbon::createFromTimestamp(strtotime($broadcast->fire_at_utc))->diffForHumans() !!}
                                    <br>
                                    {!! \Carbon\Carbon::createFromTimestamp(strtotime($broadcast->fire_at_utc)) !!}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Start Time</strong></td>
                            <td class="text-right">
                                @if($broadcast->start_time_utc !== null)
                                    {!! \Carbon\Carbon::createFromTimestamp(strtotime($broadcast->start_time_utc))->diffForHumans() !!}
                                    <br>
                                    {!! \Carbon\Carbon::createFromTimestamp(strtotime($broadcast->start_time_utc)) !!}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>End Time</strong></td>
                            <td class="text-right">
                                @if($broadcast->end_time_utc !== null)
                                    {!! \Carbon\Carbon::createFromTimestamp(strtotime($broadcast->end_time_utc))->diffForHumans() !!}
                                    <br>
                                    {!! \Carbon\Carbon::createFromTimestamp(strtotime($broadcast->end_time_utc)) !!}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Type</strong></td>
                            <td class="text-right">{!! $broadcast->broadcast_type !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Intention</strong></td>
                            <td class="text-right">{!! $broadcast->intention !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Facebook Type</strong></td>
                            <td class="text-right">{!! $broadcast->facebook_messaging_type !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Facebook Tag</strong></td>
                            <td class="text-right">{!! $broadcast->facebook_messaging_tag !!}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-sm-12">
            <div class="card">
                <div class="card-header">
                    Message History
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th>To</th>
                            <th class="text-right">Status</th>
                            <th class="text-right">Sent At</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($broadcast->interactions()->orderBy('uid', 'asc')->get() as $message)
                            <tr>
                                <td>
                                    <a href="/page/{!! $page->uid !!}/subscriber/{!! $message->subscriber->uid !!}">{!! $message->subscriber->first_name !!} {!! $message->subscriber->last_name !!}</a>
                                </td>
                                <td class="text-right">
                                    Sent
                                </td>
                                <td class="text-right">
                                    {!! $message->created_at_utc !!}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
