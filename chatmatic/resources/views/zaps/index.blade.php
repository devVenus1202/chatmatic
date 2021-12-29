@extends('layouts.app')

@section('title', 'Zaps - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Zap Triggers</li>
                </ol>
            </nav>
            <h3>Zap Triggers</h3>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <table class="table table-sm">
                <thead>
                <th class="text-center">UID</th>
                <th>Page</th>
                <th class="text-center">Type</th>
                <th class="text-right">Hook URL</th>
                <th class="text-right">Created</th>
                </thead>
                <tbody>
                @foreach($zaps as $zap)
                    <tr>
                        <td class="text-center"><a href="/zap/{!! $zap->uid !!}">{!! $zap->uid !!}</a></td>
                        <td><a href="/page/{!! $zap->page->uid !!}">{!! $zap->page->fb_name !!}</a></td>
                        <td class="text-center">{!! $zap->action !!}</td>
                        <td class="text-right">{!! $zap->target_url !!}</td>
                        <td class="text-right">{!! $zap->created_at_utc->diffForHumans() !!}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="text-center">
                {!! $zaps->links() !!}
            </div>
        </div>
    </div>
@endsection
