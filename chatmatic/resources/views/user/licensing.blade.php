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
                    <li class="breadcrumb-item active" aria-current="page">Licensing</li>
                </ol>
            </nav>
            <div class="row">
                <div class="col"><h3>User Licensing Detail</h3></div>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col">
            <ul class="nav nav-tabs" id="licensingTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="licences-tab" data-toggle="tab" href="#licences" role="tab" aria-controls="licences" aria-selected="true">Licences</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="charges-tab" data-toggle="tab" href="#charges" role="tab" aria-controls="charges" aria-selected="false">Charges</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="subscriptions-tab" data-toggle="tab" href="#subscriptions" role="tab" aria-controls="subscriptions" aria-selected="false">Subscriptions</a>
                </li>
            </ul>

            <div class="tab-content" id="licencesingTabsContent">
                <div class="tab-pane fade show active" id="licences" role="tabpanel" aria-labelledby="licences-tab">
                    <br>
                    <h3>Page Licenses</h3>
                    <br>
                    <table class="table table-sm">
                        <thead>
                        <th>Subscription</th>
                        <th>Page</th>
                        <th class="text-right">Created</th>
                        <th class="text-right">Updated</th>
                        </thead>
                        <tbody>
                        @foreach($user->pageLicenses as $lic)
                            <tr>
                                <td>{!! $lic->stripeSubscription->stripe_plan_id !!}</td>
                                <td>
                                    @if($lic->page_uid === null)
                                        Not Used
                                    @else
                                        <a href="/page/{!! $lic->page->uid !!}">{!! $lic->page->fb_name !!}</a>
                                    @endif
                                </td>
                                <td class="text-right">{!! $lic->created_at_utc->diffForHumans() !!}</td>
                                <td class="text-right">{!! $lic->updated_at_utc->diffForHumans() !!}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane fade" id="charges" role="tabpanel" aria-labelledby="charges-tab">
                    <br>
                    <h3>Stripe Charges</h3>
                    <br>
                    @php
                        // This is a quick and dirty job - move this to controller at some point
                        $charges = $user->stripeCharges();
                        if(isset($charges->data) && count($charges->data) > 0)
                            $charges = $charges->data;
                        else
                            $charges = null;
                    @endphp
                    @if($charges !== null)
                        <table class="table table-sm">
                            <thead>
                            <th>ID</th>
                            <th>Type</th>
                            <th class="text-right">Amount</th>
                            <th class="text-right">Refunded</th>
                            <th class="text-center">Captured</th>
                            <th class="text-right">Created</th>
                            <th class="text-center">Currency</th>
                            <th class="text-center">Paid</th>
                            <th class="text-right">Outcome</th>
                            </thead>
                            <tbody>
                            @foreach($charges as $chargeObj)
                                <tr>
                                    <td>{!! $chargeObj->id !!}</td>
                                    <td>{!! $chargeObj->object !!}</td>
                                    <td class="text-right">
                                        @if($chargeObj->amount > 0)
                                            ${!! ($chargeObj->amount / 100) !!}
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        @if($chargeObj->amount_refunded > 0)
                                            ${!! ($chargeObj->amount_refunded / 100) !!}
                                        @else

                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($chargeObj->captured)
                                            <span class="badge badge-success">Yes</span>
                                        @else
                                            <span class="badge badge-danger">No</span>
                                        @endif
                                    </td>
                                    <td class="text-right">{!! \Carbon\Carbon::createFromTimestamp($chargeObj->created)->toDateTimeString() !!}</td>
                                    <td class="text-center">{!! $chargeObj->currency !!}</td>
                                    <td class="text-center">
                                        @if($chargeObj->paid)
                                            <span class="badge badge-success">Yes</span>
                                        @else
                                            <span class="badge badge-danger">No</span>
                                        @endif
                                    </td>
                                    <td>
                            <pre>
                                {!! print_r($chargeObj->outcome, true) !!}
                            </pre>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @else
                        <p>No charges for this user.</p>
                    @endif
                </div>
                <div class="tab-pane fade" id="subscriptions" role="tabpanel" aria-labelledby="subscriptions-tab">
                    <br>
                    <h3>Stripe Subscriptions</h3>
                    <br>
                    <table class="table table-sm">
                        <thead>
                        <th class="text-center">Status</th>
                        <th>Plan</th>
                        <th class="text-right">Price</th>
                        <th class="text-center">Coupon Code</th>
                        <th class="text-right">Created</th>
                        <th class="text-right">Renews</th>
                        <th></th>
                        </thead>
                        <tbody>
                        @foreach($user->stripeSubscriptions as $subscription)
                            <tr>
                                <td class="text-center">
                                    {!! $subscription->status !!} <br>
                                    Stripe: {!! $subscription->getStripeData()->status !!}
                                </td>
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
                </div>
            </div>
        </div>
    </div>

@endsection