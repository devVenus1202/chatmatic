<?php

namespace App\Http\Controllers\API;

use App\StripeSubscription;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CouponController extends BaseController
{

    public function check(Request $request, $page_uid)
    {
        $response = [
            'success'       => 0,
            'error'         => 0,
            'error_msg'     => '',
            'price'  => [],
        ];

        $user = $this->user;
        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        // Get request vars
        $plan_type      = $request->get('plan');
        $coupon         = $request->get('coupon');

        // What kind of plan are we trying to purchase? Determine and obtain the plan from the database
        $plan       = '';
        switch($plan_type)
        {
            case 'monthly':
                $plan       = 'monthly_single-fan-page';
                break;

            case 'yearly':
                $plan       = 'yearly_single-fan-page';
                break;
        }

        // Get the coupon from the database
        $coupon_object = \DB::table('stripe_coupons')->where('coupon_code', $coupon)->first();
        if( ! $coupon_object)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Coupon code is invalid.';

            return $response;
        }

        // Determine if this coupon can be used for this plan
        if($coupon_object->stripe_plan_id !== $plan)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'This coupon is not valid for this plan.';

            return $response;
        }

        // Determine if this account has used this coupon yet
        $coupon_check = \DB::table('chatmatic_user_stripe_coupon_usages')->where('coupon_code', $coupon)->where('chatmatic_user_uid', $user->uid)->first();
        if($coupon_check)
        {
            // Coupon has been used, let's determine if it's over it's limit
            $coupon_limit = $coupon_object->maximum_uses_per_account;

            if($coupon_check->usages >= $coupon_limit)
            {
                $response['error'] = 1;
                $response['error_msg'] = 'This coupon has reached it\'s maximum number of uses.';

                return $response;
            }
        }

        // Get coupon details from Stripe to determine the amount off
        $stripe_key         = \Config::get('chatmatic.services.stripe.secret');
        \Stripe\Stripe::setApiKey($stripe_key);
        $stripe_coupon      = \Stripe\Coupon::retrieve($coupon);

        $discount_percent   = $stripe_coupon->percent_off;

        // Get the plan from Stripe to determine the base price
        $stripe_plan        = \Stripe\Plan::retrieve($plan);
        $plan_base_amount   = $stripe_plan->amount;

        // Calculate the new price if this coupon is valid
        $discount_amount    = $plan_base_amount *  ($discount_percent / 100);
        $price              = $plan_base_amount - $discount_amount;

        // Return the new price
        $response['success'] = 1;
        $response['price'] = $price;

        return $response;
    }
}
