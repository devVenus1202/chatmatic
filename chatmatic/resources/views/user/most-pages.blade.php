@extends('layouts.app')

@section('title', 'Users - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Users</li>
                </ol>
            </nav>
            <div class="row">
                <div class="col"><h3>Users (Top 100)</h3></div>
                <div class="col text-right">
                    <a href="/users/most-pages" class="btn btn-primary btn-sm">Most Pages</a>
                    <a href="/users/most-connected-pages" class="btn btn-primary btn-sm">Most Connected Pages</a>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <table class="table table-sm">
                <thead>
                <th>Email</th>
                <th>Name</th>
                <th class="text-center">Pages</th>
                <th class="text-center">Connected Pages</th>
                <th class="text-right">Created</th>
                </thead>
                <tbody>
                @foreach($users as $user)
                    <tr>
                        <td><a href="/user/{!! $user->uid !!}">{!! $user->facebook_email !!}</a></td>
                        <td><a href="/user/{!! $user->uid !!}">{!! $user->facebook_name !!}</a></td>
                        <td class="text-center">{!! $user->pages()->count() !!}</td>
                        <td class="text-center">{!! $user->pages()->where('is_connected', 1)->count() !!}</td>
                        <td class="text-right">{!! $user->created_at_utc->diffForHumans() !!}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection