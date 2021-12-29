@extends('layouts.app')

@section('title', 'Triggers - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/pages">Pages</a></li>
                    <li class="breadcrumb-item"><a href="/page/{!! $page->uid !!}">{!! $page->fb_name !!}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Triggers</li>
                </ol>
            </nav>
            <h3>Triggers</h3>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <table class="table table-sm">
                <thead>
                <th>UID</th>
                <th>Post</th>
                <th>Active</th>
                <th>Inclusion Keywords</th>
                <th>Exclusion Keywords</th>
                <th>Message</th>
                <th class="text-right">Comments</th>
                <th class="text-right">Inclusion Match Count</th>
                <th class="text-right">Exclusion Match Count</th>
                <th class="text-right">Inclusion Non Match Count</th>
                <th class="text-right">Acceptable and No Inclusion Count</th>
                <th class="text-right">Messages Sent</th>
                <th class="text-right">Messages Opened</th>
                </thead>
                <tbody>
                @foreach($page->triggers()->orderBy('uid', 'DESC')->get() as $trigger)
                    <tr>
                        <td>{!! $trigger->uid !!}</td>
                        <td>{!! $trigger->post->uid !!}</td>
                        <td>
                            @if($trigger->active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Disabled</span>
                            @endif
                        </td>
                        <td>{!! $trigger->inclusion_keywords !!}</td>
                        <td>{!! $trigger->exclusion_keywords !!}</td>
                        <td>{!! $trigger->message !!}</td>
                        <td class="text-right">{!! $trigger->comments !!}</td>
                        <td class="text-right">{!! $trigger->inclusion_match_count !!}</td>
                        <td class="text-right">{!! $trigger->exclusion_match_count !!}</td>
                        <td class="text-right">{!! $trigger->inclusion_non_match_count !!}</td>
                        <td class="text-right">{!! $trigger->acceptable_and_no_inclusion_count !!}</td>
                        <td class="text-right">{!! $trigger->messages_sent !!}</td>
                        <td class="text-right">{!! $trigger->messages_opened !!}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
