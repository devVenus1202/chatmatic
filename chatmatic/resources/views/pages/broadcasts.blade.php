@extends('layouts.app')

@section('title', 'Broadcasts - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/pages">Pages</a></li>
                    <li class="breadcrumb-item"><a href="/page/{!! $page->uid !!}">{!! $page->fb_name !!}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Broadcasts</li>
                </ol>
            </nav>
            <h3>Broadcasts</h3>
        </div>
    </div>
    @include('broadcasts.partials.broadcasts-table')
@endsection
