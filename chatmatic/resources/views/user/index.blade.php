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
                <div class="col"><h3>Users</h3></div>
                <div class="col text-right">
                    <form action="/users" class="form-inline float-right">
                        <label class="sr-only" for="search_value">Name</label>
                        <input type="text" class="form-control mb-2 mr-sm-2" id="search_value" name="search" placeholder="Search...">
                        <button type="submit" class="btn btn-primary mb-2">Search</button>
                    </form>
                </div>
            </div>
            <div class="row">
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
                <th>Referral</th>
                <th class="text-center">Subscription</th>
                <th class="text-center">Pages</th>
                <th class="text-center">Connected Pages</th>
                <th class="text-right">Created</th>
                </thead>
                <tbody>
                @foreach($users as $user)
                    <tr class="@if($user->stripeSubscriptions()->count() > 0) table-success @endif">
                        <td><a href="/user/{!! $user->uid !!}">{!! $user->facebook_email !!}</a></td>
                        <td><a href="/user/{!! $user->uid !!}">{!! $user->facebook_name !!}</a></td>
                        <td>{!! $user->referred !!}</td>
                        <td class="text-center">
                            @if($user->stripeSubscriptions()->count() > 0)
                                {!! $user->usedPageLicenses()->count() !!} / {!! $user->unusedPageLicenses()->count() !!} Used
                            @else
                                &nbsp;
                            @endif
                        </td>
                        <td class="text-center">{!! $user->pages()->count() !!}</td>
                        <td class="text-center">{!! $user->pages()->where('is_connected', 1)->count() !!}</td>
                        <td class="text-right">{!! $user->created_at_utc->diffForHumans() !!}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <nav aria-label="Pagination">
                {!! $users->links() !!}
            </nav>
        </div>
    </div>
@endsection