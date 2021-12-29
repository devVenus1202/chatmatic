@extends('layouts.app')

@section('title', 'User Detail - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/users">Users</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{!! $user->facebook_name !!}</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    User Details
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                        <tr>
                            <td><strong>Chatmatic User ID</strong></td>
                            <td class="text-right">{!! $user->uid !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Stripe Subscription</strong></td>
                            <td class="text-right">
                                @if($user->stripeSubscriptions()->count() > 0)
                                    Yes - <a href="/user/{!! $user->uid !!}/licensing">View</a> <br>
                                    Used: {!! $user->usedPageLicenses()->count() !!} |
                                    Avail: {!! $user->unusedPageLicenses()->count() !!}
                                @else
                                    No
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Facebook UID</strong></td>
                            <td class="text-right">{!! $user->facebook_user_id !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Facebook Name</strong></td>
                            <td class="text-right">{!! $user->facebook_name !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Facebook Email</strong></td>
                            <td class="text-right">{!! $user->facebook_email !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Referral</strong></td>
                            <td class="text-right">{!! $user->referred !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Created</strong></td>
                            <td class="text-right">{!! $user->created_at_utc->format("m/d/Y") !!} / {!! $user->created_at_utc->diffForHumans() !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Last Login</strong>&nbsp; <small><a href="{!! $user->uid !!}/sessions">(View sessions)</a></small></td>
                            <td class="text-right">
                                @if($user->authTickets()->orderBy('updated_at_utc', 'desc')->first())
                                    {!! $user->authTickets()->orderBy('updated_at_utc', 'desc')->first()->updated_at_utc !!}
                                @else
                                    
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Pages</strong></td>
                            <td class="text-right">{!! $user->pages()->count() !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Connected Pages</strong></td>
                            <td class="text-right">{!! $user->pages()->where('is_connected', 1)->count() !!}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    User Permissions
                </div>
                <div class="card-body">
                    @php
                        $permissions = $user->permissions();
                    @endphp
                    @if( ! isset($permissions['error']))
                        <table class="table table-sm">
                            <tbody>
                        @foreach($user->permissions() as $permission)
                                <tr>
                                    <td><strong>{!! $permission['permission'] !!}</strong></td>
                                    <td class="text-right">{!! $permission['status'] !!}</td>
                                </tr>
                        @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-warning">{!! $permissions['message'] !!}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <br>

    <div class="row">
        <div class="col-12">
            <div class="alert alert-warning" role="alert" style="overflow: auto">
                <strong>Facebook Token: </strong>{!! $user->facebook_long_token !!} <br/>
                <strong>Chatmatic Login Token: </strong>{!! $user->api_token !!} <br/>
                <strong>Chatmatic External API Token: </strong>{!! $user->ext_api_token !!}
            </div>
        </div>
    </div>

    <div class="row py-4">
        <div class="col">
            <h3>Pages</h3>
            @include('pages.partials.pages-table', ['pages' => $pages])
        </div>
    </div>
@endsection