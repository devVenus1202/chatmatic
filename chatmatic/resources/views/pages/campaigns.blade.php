@extends('layouts.app')

@section('title', 'Flow Triggers - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/pages">Pages</a></li>
                    <li class="breadcrumb-item"><a href="/page/{!! $page->uid !!}">{!! $page->fb_name !!}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Flow Triggers</li>
                </ol>
            </nav>
            <h3>Flow Triggers</h3>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <table class="table table-sm">
                <thead>
                <th>Name</th>
                <th>Type</th>
                <th>Flow Name</th>
                <th class="text-center">Archived</th>
                <th class="text-center">Messages Delivered</th>
                <th class="text-center">Messages Read</th>
                <th class="text-center">Messages Clicked</th>
                <th class="text-right">Created</th>
                </thead>
                <tbody>
                @foreach($page->workflowTriggers()->orderBy('uid', 'DESC')->get() as $trigger)
                    <tr>
                        <td><a href="/page/{!! $page->uid !!}/flow_trigger/{!! $trigger->uid !!}">{!! $trigger->name !!}</a></td>
                        <td>{!! $trigger->type !!}</td>
                        @if (isset($trigger->workflow))
                            <td>{!! $trigger->workflow->name !!}</td>
                        @else
                            <td>No workflow associated</td>
                        @endif
                        <td class="text-center">@if($trigger->archived) <span class="badge badge-danger">Yes</span> @else <span class="badge badge-success">No</span> @endif</td>
                        <td class="text-center">{!! $trigger->messages_delivered !!}</td>
                        <td class="text-center">{!! $trigger->messages_read !!}</td>
                        <td class="text-center">{!! $trigger->messages_clicked !!}</td>
                        <td class="text-right">{!! $trigger->created_at_utc !!}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
