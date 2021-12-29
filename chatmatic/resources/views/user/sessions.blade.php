@extends('layouts.app')

@section('title', 'User Licensing - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/users">Users</a></li>
                    <li class="breadcrumb-item"><a href="/user/{!! $user->uid !!}">{!! $user->facebook_name !!}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Sessions</li>
                </ol>
            </nav>
            <div class="row">
                <div class="col"><h3>User Login Sessions Detail</h3></div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <h3>Login Sessions</h3>
            <table class="table table-sm">
                <thead>
                <th class="text-center">Session ID</th>
                <th>IP Address</th>
                <th class="text-right">Created</th>
                <th class="text-right">Updated (Last logged in)</th>
                </thead>
                <tbody>
                @foreach($user->authTickets()->orderBy('updated_at_utc', 'desc')->get() as $auth_ticket)
                    <tr>
                        <td class="text-center">{!! $auth_ticket->uid !!}</td>
                        <td>{!! $auth_ticket->ip_address !!}</td>
                        <td class="text-right">{!! $auth_ticket->created_at_utc !!}</td>
                        <td class="text-right">{!! $auth_ticket->updated_at_utc !!}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection