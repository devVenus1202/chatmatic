<?php

namespace App\Http\Controllers\API;

use App\StripeSubscription;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LicenseController extends BaseController
{
    
    /**
     * @param Request $request
     * @param $page_uid
     * @return array
     * @throws \Exception
     */
    public function create(Request $request, $page_uid)
    {
        $response = [
            'success'       => 0,
            'error'         => 0,
            'error_msg'     => '',
            'billing_info'  => [],
        ];

        \DB::beginTransaction();
        $user = $this->user;
        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        // First let's determine if this page already has an active licence to make sure we're not getting a doubled up request
        $page_licence = $page->licenses()->first();
        if($page_licence)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'This page is already licenced';

            return $response;
        }

        // Get request vars
        $plan_type      = $request->get('plan');
        $payment_source = $request->get('src');
        $coupon         = $request->get('coupon');

        // What kind of plan are we trying to purchase? Determine and obtain the plan from the database
        $plan       = '';
        $plan_label = '';
        switch($plan_type)
        {
            case 'monthly':
                $plan       = 'monthly_single-fan-page';
                $plan_label = '1 Fan Page - Monthly';
                break;

            case 'yearly':
                $plan       = 'yearly_single-fan-page';
                $plan_label = '1 Fan Page - Yearly';
                break;
        }

        // Are we using a coupon?
        if($coupon)
        {
            // Get the coupon from the database
            $coupon_object = \DB::table('stripe_coupons')->where('coupon_code', $coupon)->first();
            if( ! $coupon_object)
            {
                $response['error'] = 1;
                $response['error_msg'] = 'Coupon code is invalid.';

                \DB::rollBack();
                return $response;
            }

            // Determine if this coupon can be used for this plan
            if($coupon_object->stripe_plan_id !== $plan)
            {
                $response['error'] = 1;
                $response['error_msg'] = 'This coupon is not valid for this plan.';

                \DB::rollBack();
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

                    \DB::rollBack();
                    return $response;
                }
            }
        }

        // Do we already have a stripe user for this user?
        $stripe_customer_id = $user->stripe_customer_id;
        if($stripe_customer_id === null)
        {
            try{
                $user->createStripeAccount($payment_source);
                $stripe_customer_id = $user->stripe_customer_id;
            }catch(\Exception $e)
            {
                $response['error'] = 1;
                $response['error_msg'] = $e->getMessage();

                \DB::rollBack();
                return $response;
            }
        }
        else // User has a Stripe account - let's make this source the default
        {
            try{
                $user->updateStripeDefaultSource($payment_source);
            }catch(\Stripe\Error\Card $e) {
                $response['error'] = 1;
                $response['error_msg'] = $e->getMessage();

                \DB::rollBack();
                return $response;
            }
        }

        // Create the subscription record
        try{
            $stripe_subscription_object = $user->createStripeSubscription($plan, $coupon);
        }catch(\Exception $e)
        {
            $response['error']      = 1;
            $response['error_msg']  = $e->getMessage();

            \DB::rollBack();
            return $response;
        }

        $stripe_customer_object = \Stripe\Customer::retrieve($stripe_customer_id);

        // Get the subscription id from the return object
        $stripe_subscription_id     = $stripe_subscription_object->id;

        // Determine the price charged/paying
        $price = $stripe_subscription_object->plan->amount;
        // If there's a coupon in use we'll check the 'discount' object on the subscription to determine the discount
        if($coupon)
        {
            if(isset($stripe_subscription_object->discount))
            {
                // Grab discount percentage
                $discount_amount = $stripe_subscription_object->discount->coupon->percent_off;

                // Determine how much of our price will be deducted by this discount percentage
                $discounted_value = $price * ($discount_amount / 100); // Divide by 100 to convert integer to percent context

                // Discount that value from the base price to determine what this user will be paying
                $price = $price - $discounted_value;
            }
        }

        // Create the subscription record in our database
        // Determine largest uid (we shouldn't need to do this but for some reason the uid isn't auto-populating on these queries)
        $highest_subscription_uid = StripeSubscription::orderBy('uid', 'desc')->first();
        $uid = 1;
        if($highest_subscription_uid)
        {
            $uid = $highest_subscription_uid->uid + 1;
        }

        // The database, prior to this API, had an empty string in the place of a coupon code for subscriptions without coupons, so replicating that here
        if($coupon === null)
            $coupon = '';

        $subscription = $user->stripeSubscriptions()->create([
            'uid'                       => $uid,
            'stripe_subscription_id'    => $stripe_subscription_id,
            'stripe_plan_id'            => $plan,
            'status'                    => 'active',
            'label'                     => $plan_label,
            'subscription_renewal_utc'  => Carbon::now()->toDateTimeString(),
            'price'                     => $price,
            'coupon_code'               => $coupon
        ]);

        // Refer to previous comment about $coupon
        if($coupon === '')
            $coupon = null;

        // Create the page licence record
        // First we need to know how many page licences go with this plan - we'll grab the plan id and check that record
        $stripe_plan_id = $subscription->stripe_plan_id;
        $stripe_plan    = \DB::table('stripe_plans')->where('stripe_plan_id', $stripe_plan_id)->first();
        $licence_count  = $stripe_plan->licenses;
        // Now we know how many licences this plan allows for, we'll insert them
        $inserted_count = 0;
        while($inserted_count < $licence_count)
        {
            // Determine highest uid
            $uid = 1;
            $highest_page_licence_uid = \DB::table('chatmatic_page_licenses')->select('uid')->orderBy('uid', 'desc')->first();
            if($highest_page_licence_uid)
            {
                $uid = $highest_page_licence_uid->uid + 1;
            }
            $chatmatic_page_licence = [
                'uid'                       => $uid,
                'stripe_subscription_uid'   => $subscription->uid,
                'page_uid'                  => null,
                'created_at_utc'            => Carbon::now()->toDateTimeString(),
                'updated_at_utc'            => Carbon::now()->toDateTimeString(),
            ];

            $inserted = \DB::table('chatmatic_page_licenses')->insert($chatmatic_page_licence);

            $inserted_count++;
            unset($chatmatic_page_licence);
        }

        // Now we need to apply one of the licences to the page so we'll first grab an un-used licence
        $chatmatic_page_licence = \DB::table('chatmatic_page_licenses')
            ->where('stripe_subscription_uid', $subscription->uid)
            ->where('page_uid', null)
            ->first();

        if( ! $chatmatic_page_licence)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'There are no available licences to associate with this page.';

            \DB::rollBack();
            return $response;
        }

        // And now we'll associate this page with it
        $updated = \DB::table('chatmatic_page_licenses')
            ->where('uid', $chatmatic_page_licence->uid)
            ->update([
                'page_uid'  => $page->uid
            ]);

        // Apply the coupon usage to their account (chatmatic_user_stripe_coupon_usages)
        if($coupon)
        {
            // Create the usage record, or update the existing
            $coupon_usage = \DB::table('chatmatic_user_stripe_coupon_usages')
                ->where('coupon_code', $coupon)
                ->where('chatmatic_user_uid', $user->uid)
                ->first();
            if( ! $coupon_usage)
            {
                // There isn't a record yet, create one
                $coupon_usage = [
                    'chatmatic_user_uid'    => $user->uid,
                    'coupon_code'           => $coupon,
                    'usages'                => 1,
                    'created_at_utc'        => Carbon::now()->toDateTimeString(),
                    'updated_at_utc'        => Carbon::now()->toDateTimeString(),
                ];

                $inserted = \DB::table('chatmatic_user_stripe_coupon_usages')->insert($coupon_usage);
            }
            else
            {
                // Usage record exists, let's update it
                $existing_count = $coupon_usage->usages;
                $updated = \DB::table('chatmatic_user_stripe_coupon_usages')
                    ->where('coupon_code', $coupon)
                    ->where('chatmatic_user_uid', $user->uid)
                    ->update([
                        'usages'            => $existing_count + 1,
                        'updated_at_utc'    => Carbon::now()->toDateTimeString()
                    ]);
            }
        }

        // Get billing details from customer object
        $source = $stripe_customer_object->sources->retrieve($payment_source);
        $card = $source->card;

        $response['success']        = 1;
        $response['billing_info']   = [
            'name'          => $plan_label,
            'start_date'    => $subscription->created_at_utc->toDateTimeString(),
            'email'         => $user->facebook_email,
            'card_number'   => 'xxxx-xxxx-xxxx-'.$card->last4,
            'card_exp'      => $card->exp_month.'/'.$card->exp_year,
            'price'         => $price
        ];

        \DB::commit();

        return $response;
    }

    /**
     * Update billing/card details
     *
     * @param Request $request
     * @param $page_uid
     * @return \App\Page|array
     */
    public function update(Request $request, $page_uid)
    {
        $response = [
            'success'       => 0,
            'error'         => 0,
            'error_msg'     => '',
            'billing_info'  => [],
        ];

        $user = $this->user;
        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        // Get the new payment source
        $payment_source = $request->get('src');

        try{
            $user->updateStripeDefaultSource($payment_source);
        }catch(\Stripe\Error\Card $e) {
            $response['error'] = 1;
            $response['error_msg'] = $e->getMessage();

            return $response;
        }

        // Does this page have a license?
        $license = $page->licenses()->first();

        // If the page has no license, return that
        if( ! $license)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Page licence not found.';

            return $response;
        }

        // Page has a license, let's obtain the billing details
        $stripe_subscription_uid = $license->stripe_subscription_uid;

        // Get the subscription record
        $subscription = StripeSubscription::find($stripe_subscription_uid);

        // Get the user associated with the record
        $subscriber_user = $subscription->user;

        // Get billing details from customer object
        $card = $subscriber_user->getStripeCard();

        // Get the plan name from the subscription
        $stripe_plan_id = $subscription->stripe_plan_id;
        $plan_label = '';
        switch($stripe_plan_id)
        {
            case 'monthly_single-fan-page':
                $plan_label = '1 Fan Page - Monthly';
                break;

            case 'yearly_single-fan-page':
                $plan_label = '1 Fan Page - Yearly';
                break;
        }

        $response['success']        = 1;
        $response['billing_info']   = [
            'name'          => $plan_label,
            'start_date'    => $subscription->created_at_utc->toDateTimeString(),
            'email'         => $user->facebook_email,
            'card_number'   => 'xxxx-xxxx-xxxx-'.$card->last4,
            'card_exp'      => $card->exp_month.'/'.$card->exp_year,
            'price'         => $subscription->price
        ];

        return $response;
    }

    /**
     * Cancel a subscription/page licence
     *
     * @param Request $request
     * @param $page_uid
     * @return \App\Page|array
     * @throws \Exception
     */
    public function delete(Request $request, $page_uid)
    {
        $response = [
            'success'       => 0,
            'error'         => 0,
            'error_msg'     => '',
        ];

        $user = $this->user;
        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        // Find the subscription related to this page
        $license = $page->licenses()->first();

        // If the page has no license, return that
        if( ! $license)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Page licence not found.';

            return $response;
        }

        // Page has a license, let's obtain the billing details
        $stripe_subscription_uid = $license->stripe_subscription_uid;

        // Get the subscription record
        $subscription = StripeSubscription::find($stripe_subscription_uid);

        // Cancel the subscription with Stripe
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        $stripe_subscription_object = \Stripe\Subscription::retrieve($subscription->stripe_subscription_id);
        $stripe_subscription_object->cancel();

        // Remove page licence
        $license->delete();

        // Update subscription to canceled
        $subscription->status = 'cancelled';
        $subscription->save();

        $response['success'] = 1;

        return $response;
    }

    /**
     * @param Request $request
     * @param $page_uid
     * @return \App\Page|array
     */
    public function billingInfo(Request $request, $page_uid)
    {
        $response = [
            'success'       => 0,
            'error'         => 0,
            'error_msg'     => '',
            'billing_info'  => [],
        ];

        $user = $this->user;
        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        // Does this page have a license?
        $license = $page->licenses()->first();

        // If the page has no license, return that
        if( ! $license)
        {
            $response['success'] = 1;

            return $response;
        }

        if ($license->appsumo_user_uid){

            $sumo_user = \App\AppSumoUser::find($license->appsumo_user_uid);

            if (! $sumo_user){
                $response['error']      = 1;
                $response['error_msg']  = 'Sumo User not found';

                return $response;
            }

            switch ($sumo_user->plan_id) {
                case 'chatmatic_tier1':
                    $available_licenses = 1;
                    break;

                case 'chatmatic_tier2':
                    $available_licenses = 10;
                    break;

                case 'chatmatic_tier3':
                    $available_licenses = 25;
                    break;
                
                case 'chatmatic_tier4':
                    $available_licenses = 50;
                    break;

                case 'chatmatic_tier5':
                    $available_licenses = 100;
                    break;

                default:
                    $available_licenses = 0;
                    break;
            }



            $response['success']        = 1;
            $response['billing_info']   = [
                'name'                  => $sumo_user->plan_id,
                'start_date'            => $sumo_user->created_at_utc,
                'email'                 => $sumo_user->email,
                'used_licenses'         => $sumo_user->used_licenses,
                'available_licenses'    => $available_licenses
            ];

        }else{

            // Page has a license, let's obtain the billing details
            $stripe_subscription_uid = $license->stripe_subscription_uid;

            // Get the subscription record
            $subscription = StripeSubscription::find($stripe_subscription_uid);

            // Get the user associated with the record
            $subscriber_user = $subscription->user;

            // Get billing details from customer object
            $card = $subscriber_user->getStripeCard();

            // Get the plan name from the subscription
            $stripe_plan_id = $subscription->stripe_plan_id;
            $plan_label = '';
            switch($stripe_plan_id)
            {
                case 'monthly_single-fan-page':
                    $plan_label = '1 Fan Page - Monthly';
                    break;

                case 'yearly_single-fan-page':
                    $plan_label = '1 Fan Page - Yearly';
                    break;
            }

            $response['success']        = 1;
            $response['billing_info']   = [
                'name'          => $plan_label,
                'start_date'    => $subscription->created_at_utc->toDateTimeString(),
                'email'         => $user->facebook_email,
                'card_number'   => 'xxxx-xxxx-xxxx-'.$card->last4,
                'card_exp'      => $card->exp_month.'/'.$card->exp_year,
                'price'         => $subscription->price
            ];            

	}

        return $response;
    }


    public function license_page(Request $request, $page_uid)
    {
        $response = [
            'success'       => 0,
            'error'         => 0,
            'error_msg'     => '',
        ];


        $user = $this->user;
        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        // Does this page have a license?
        $license = $page->licenses()->first();

        if ($license)
        {
            $response['error']          = 1;
            $response['error_msg']      = 'This page is already licensed';

            return $response;
        }

        // Let's check if the user has assigned an appsumo user
        $app_sumo_user = $user->sumoUser()->first();

        if ( ! $app_sumo_user)
        {
            $response['error']          = 1;
            $response['error_msg']      = 'This user has not an app sumo account';

            return $response;
        }

        // Check if this user another license to be used
        $max_licensing_limit = 0;

        switch ($app_sumo_user->plan_id) {
            case "chatmatic_tier1":
                $max_licensing_limit = 1;
                break;

            case "chatmatic_tier2":
                $max_licensing_limit = 10;
                break;

            case "chatmatic_tier3":
                $max_licensing_limit = 25;
                break;

            case "chatmatic_tier4":
                $max_licensing_limit = 50;
                break;

            case "chatmatic_tier5":
                $max_licensing_limit = 100;
                break;
        }

        if ($app_sumo_user->used_licenses >= $max_licensing_limit)
        {
            $response['error']           = 1;
            $response['error_msg']       = 'This plan has no remamining licenses';

            return $response;
        }

        // Let's create the license register
        $last_record = \DB::table('chatmatic_page_licenses')->select('uid')->orderBy('uid', 'desc')->first();

        $chatmatic_page_licence = [
                'uid'                       => $last_record->uid+1,
                'stripe_subscription_uid'   => 0, // zero por app sumo users
                'page_uid'                  => $page->uid,
                'appsumo_user_uid'          => $app_sumo_user->uid,
                'created_at_utc'            => Carbon::now()->toDateTimeString(),
                'updated_at_utc'            => Carbon::now()->toDateTimeString(),
        ];

        $inserted = \DB::table('chatmatic_page_licenses')->insert($chatmatic_page_licence);

        if ( ! $inserted)
        {
            $response['error']          = 1;
            $response['error_msg']      = 'License not created using app sumo';
        }

        // Now let's update the app sumo user record
        $app_sumo_user->used_licenses += 1;
        $app_sumo_user->save();


        $response['success']        = 1;

        return $response;

    }

    public function appsumo_license_info(Request $request){
        
        /** @var \App\User $user */
        $user = $this->user;

        // Getting the app sumo user
        $sumo_user = $user->sumoUser;
        if ( $user->sumoUser){

            if ( $user->sumoUser->refunded){
                $response = [
                    'plan_id' => 'canceled',
                    'used_licenses' => 'NA',
                    'cloned_templates' => 'NA'
                ];
                return $response;

            }else{
                $response = [
                            'plan_id' => $sumo_user->plan_id,
                            'used_licenses' => $sumo_user->used_licenses,
                            'cloned_templates' => $sumo_user->cloned_templates,
                        ];
                return $response;
            }

        }else{
            $response = [
                            'error'             => 1,
                            'message'           => 'No sumo user associated with this account'
                        ];
            return $response;
        }
        
    }
}
