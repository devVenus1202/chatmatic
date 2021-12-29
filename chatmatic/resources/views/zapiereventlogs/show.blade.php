@extends('layouts.app')

@section('title', 'Zapier Event Detail - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/zapiereventlogs">Zapier Events</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Zapier Event Detail</li>
                </ol>
            </nav>
            <h3>Zapier Event Detail</h3>
        </div>
    </div>

    @include('zapiereventlogs.partials.event-details')

@endsection
