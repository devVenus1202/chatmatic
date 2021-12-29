<?php

namespace App\Http\Controllers;

use App\StripeSubscription;
use Illuminate\Http\Request;

class StripeSubscriptionsController extends Controller
{

    public function index(Request $request)
    {

        $subscriptions = StripeSubscription::orderBy('uid', 'desc');

        if($request->has('paid'))
        {
            if($request->get('paid') == 'true')
                $subscriptions = $subscriptions->where('price', '>', '0');
            else
                $subscriptions = $subscriptions->where('price', '0');
        }
        if($request->has('coupon'))
            $subscriptions = $subscriptions->where('coupon_code', $request->get('coupon'));

        $subscriptions = $subscriptions->paginate(25);

        return view('subscriptions.index')
            ->with('subscriptions', $subscriptions);
    }

    public function delete(Request $request, $subscription_uid)
    {
        $sub = StripeSubscription::find($subscription_uid);

        foreach($sub->licences as $licence)
        {
            $licence->delete();
        }

        $sub->delete();

        return redirect()->back();
    }
}
