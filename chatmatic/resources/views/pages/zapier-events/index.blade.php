@extends('layouts.app')

@section('title', 'Zapier Events - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/pages">Pages</a></li>
                    <li class="breadcrumb-item"><a href="/page/{!! $page->uid !!}">{!! $page->fb_name !!}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Zapier Events</li>
                </ol>
            </nav>
            <h3>Zapier Events</h3>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <table class="table table-sm">
                <thead>
                <tr>
                    <th class="text-center">UID</th>
                    <th class="text-center">Type</th>
                    <th class="text-right">Action</th>
                    <th class="text-right">Created</th>
                </tr>
                </thead>
                <tbody>
                @foreach($events as $event)
                    <tr>
                        <td class="text-center"><a href="/page/{!! $page->uid !!}/zapier/events/{!! $event->uid !!}">{!! $event->uid !!}</a></td>
                        <td class="text-center">{!! $event->event_type !!}</td>
                        <td class="text-right">{!! $event->action !!}</td>
                        <td class="text-right">{!! $event->created_at_utc->diffForHumans() !!}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="text-center">
                {!! $events->links() !!}
            </div>
        </div>
    </div>
@endsection
