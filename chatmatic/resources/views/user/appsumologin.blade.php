@extends('layouts.app')

@section('title', 'AppSumo Users')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><a href="/appsumo_users">AppSumo Users</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Sumo User</li>
                </ol>
            </nav>
            
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div>{!! $login_url !!}</div>            
        </div>
    </div>
@endsection