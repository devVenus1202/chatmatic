@extends('layouts.app')

@section('title', 'Users - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Subscriptions</li>
                </ol>
            </nav>
            <div class="row">
                <div class="col-7"><h3>Subscriptions</h3></div>
                <div class="col-5">
                    <div class="card">
                        <div class="card-header">
                            Subscriptions Quick-look
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tbody>
                                <tr>
                                    <td><strong>Paid Subscriptions</strong></td>
                                    <td class="text-right"><a href="?paid=true">{!! number_format(\App\StripeSubscription::where('price', '>', '0')->count()) !!}</a></td>
                                </tr>
                                <tr>
                                    <td><strong>Free Subscriptions</strong></td>
                                    <td class="text-right"><a href="?paid=false">{!! number_format(\App\StripeSubscription::where('price', '0')->count()) !!}</a></td>
                                </tr>
                                <tr>
                                    <td><strong>Coupons Used</strong></td>
                                    <td>
                                        @foreach(\DB::table('stripe_subscriptions')->select('coupon_code')->distinct()->get() as $coupon_code)
                                            @if(mb_strlen($coupon_code->coupon_code) > 0)
                                            {!! $coupon_code->coupon_code !!}
                                            <span class="pull-right">
                                                <a href="?coupon={!! $coupon_code->coupon_code !!}">
                                                    {!! \DB::table('stripe_subscriptions')->select('uid')->where('coupon_code', $coupon_code->coupon_code)->count() !!}
                                                </a>
                                            </span><br>
                                            @endif
                                        @endforeach
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <table class="table table-sm">
                <thead>
                <th>User</th>
                <th class="text-center">Status</th>
                <th>Plan</th>
                <th class="text-right">Price</th>
                <th class="text-center">Coupon Code</th>
                <th class="text-right">Created</th>
                <th class="text-right">Renews</th>
                <th></th>
                </thead>
                <tbody>
                @foreach($subscriptions as $subscription)
                    <tr>
                        <td><a href="/user/{!! $subscription->user->uid !!}">{!! $subscription->user->facebook_name !!}</a></td>
                        <td class="text-center">{!! $subscription->status !!}</td>
                        <td>
                            <small>
                            {!! $subscription->stripe_plan_id !!} <br>
                            {!! $subscription->label !!} <br>
                            {!! $subscription->stripe_subscription_id !!}
                            </small>
                        </td>
                        <td class="text-right">@if($subscription->price > 0)${!! number_format(($subscription->price / 100), 2) !!}@else 0 @endif</td>
                        <td class="text-center">{!! $subscription->coupon_code !!}</td>
                        <td class="text-right">{!! $subscription->created_at_utc->diffForHumans() !!}</td>
                        <td class="text-right">{!! $subscription->subscription_renewal_utc->diffForHumans() !!}</td>
                        <td class="text-right">
                            <a href="/subscriptions/{!! $subscription->uid !!}/delete" class="btn btn-sm btn-danger subscription-delete-button">Delete</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <nav aria-label="Pagination">
                @if(request()->has('coupon'))
                    @php
                        $appends['coupon'] = request()->get('coupon');
                    @endphp
                @endif
                @if(request()->has('paid'))
                    @php
                        $appends['paid'] = request()->get('paid');
                    @endphp
                @endif

                @if(isset($appends))
                    {!! $subscriptions->appends($appends)->links() !!}
                @else
                    {!! $subscriptions->links() !!}
                @endif
            </nav>
        </div>
    </div>
@endsection