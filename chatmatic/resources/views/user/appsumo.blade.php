@extends('layouts.app')

@section('title', 'AppSumo Users')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">AppSumo Users</li>
                </ol>
            </nav>
            
        </div>
    </div>
    <div class="row">
        <div class="col">
            <table class="table table-sm">
                <thead>
                <th>Email</th>
                <th>Plan Id</th>
                <th>Used Licenses</th>
                <th>Cloned Templates</th>
                <th class="text-center">Chatmtic User</th>
                <th class="text-right">Created</th>
                </thead>
                <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{!! $user->email !!}</td>
                        <td>{!! $user->plan_id !!}</td>
                        <td>0</td>
                        <td>0</td>
                        <td class="text-center">
                            @if ($user->chatmaticUser)
                                {!! $user->chatmaticUser->facebook_name !!}
                            @else
                                <a href="/appsumo_user/{!! $user->uid !!}">Login URL</a>
                            @endif
                        </td>
                        <td class="text-right">{!! $user->created_at_utc !!}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

        </div>
    </div>
@endsection