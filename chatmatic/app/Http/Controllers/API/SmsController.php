<?php

namespace App\Http\Controllers\API;

use App\AuditLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\SmsBalance;
use Twilio\Rest\Client;

class SmsController extends BaseController
{

    /**
     * @param Request $request
     * @param $page_uid
     * @return array
     */
    public function index(Request $request, $page_uid)
    {
        $user = $this->user;

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        $response = [
            'error'         => 0,
            'success'       => 0,
            'error_msg'     => '',
            'phone_number'  => '',
            'sms_resume'    => [],
            'sms_history'   => [],
            'autorenew'     => false,
        ];

        // First we'll check if this user already has a record on sms_balances table
        // If the user already has not a balance let's create a record with 20 boxes

        $balance = $page->sms_balance;
        if ( ! isset($balance) )
        {
            $response['success'] = 1;
            $response['sms_resume']['status'] = 'Sms account not activated';

            return $response;

        }
        else if ( $balance->total < env('TWILIO_PP_MESSAGE') )
        {
            $response['sms_resume']['over'] = True;
        }

        // Now let's retrieve the sms history
        // Last 20 sms sent
        $last_20_sms_history = $page->sms_history()->orderBy('uid', 'desc')->take(20)->get();

        foreach($last_20_sms_history as $sms)
        {
            $response['sms_history'][] = [
                "to" => $sms->to_phone_number,
                "on" => $sms->created_at_utc,
            ];
        }

        // Let's return the balance
        $response['success'] = 1;
        $response['sms_resume']['balance'] = $balance->total;

        return $response;
    }

    public function activate_free_trial(Request $request, $page_uid)
    {
        $user = $this->user;

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        $response = [
            'error'         => 0,
            'success'       => 0,
            'error_msg'     => '',
        ];

        // First we'll check if this user already has a record on sms_balances table
        // If the user already has not a balance let's create a record with 20 boxes

        $balance = $page->sms_balance;
        if ( ! isset($balance) )
        {
            $balance                            = new SmsBalance;
            $balance->total                     = 20;
            $balance->chatmatic_user_uid        = $user->uid;
            $balance->page_uid                  = $page_uid;
            $balance->autorenew                 = false;

            $new_balance_created = $balance->save();

            if ( ! isset($new_balance_created) )
            {
                $response['error']        = 1;
                $resposne['error_msg']    = 'Free trial could not be created';

                return $resposne;
            }
        }
        else
        {
            $response['error']              = 1;
            $response['error_msg']          = 'The trial was already used.';

            return $response;
        }

        $response['success'] = 1;
        
        return $response;
    }

    public function purchase_plan(Request $request, $page_uid)
    {
        // Purchase / renew plan
        $user = $this->user;

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        $response = [
            'error'                 => 0,
            'error_msg'             => 0,
            'error_msg'             => '',
            'phone_number'          => '',
            'billing_info'          =>[],
        ];

        // Let's chec we have the flag to attach a new source or not
        $new_source = $request->get('new_source');
        if( ! isset($new_source) )
        {
            $response['error']          = 1;
            $response['error_msg']      = 'The flag new source must be provided';

            return $response;
        }

        // Let's check which was the desired plan
        $desired_plan = $request->get('plan');
        if( ! isset($desired_plan) )
        {
            $response['error']          = 1;
            $response['error_msg']      = 'A plan be provided';

            return $response;
        }

        $allowed_plans = ['starter','basic','advanced'];
        if( ! in_array($desired_plan, $allowed_plans))
        {
            // workflow step item type doesn't match allowed types
            $response['error']      = 1;
            $response['error_msg']  = 'The only allowed plans are starter, basic or advanced';

            return $response;
        }

        // Get the requested vars
        $payment_source = $request->get('src');
        if( ! isset($payment_source) )
        {
            $response['error']          = 1;
            $response['error_msg']      = 'A source must be provided';

            return $response;
        }

        $autorenew = $request->get('autorenew');
        if ( ! isset($autorenew) )
        {
            $response['error']              = 1;
            $response['error_msg']          = 'Autorenew value is needed';

            return $response;
        }

        if ( ! is_bool($autorenew) )
        {
            $response['error']              = 1;
            $response['error_msg']          = 'The autorenew value is not boolean';

            return $response;
        }

        // Do we already have a stripe user for this user?
        $stripe_customer_id = $user->stripe_customer_id;
        if($stripe_customer_id === null)
        {
            $new_customer = true;
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

        // Set the amount dependint the price
        switch ($desired_plan) {
            case 'starter':
                $amount = 20;
                break;
            case 'basic':
                $amount = 40;
                break;
            case 'advanced':
                $amount = 100;
                break;
        }

        // Stripe customer object
        $stripe_key = \Config::get('chatmatic.services.stripe.secret');
        \Stripe\Stripe::setApiKey($stripe_key);
 
        if ($new_source and ! $new_customer){
            // Attache source to a customer
            \Stripe\Customer::createSource(
                $stripe_customer_id,
                    ['source' => $payment_source]
            );    
        }
         

        $stripe_customer_object = \Stripe\Customer::retrieve($stripe_customer_id);

        try{
            $charge  = \Stripe\Charge::create(['amount' => floatval($amount)*100, 
                                               'currency' => 'usd', 
                                               'customer' => $stripe_customer_id, 
                                               'source' => $payment_source, 
                                               'description' => 'Sms '.$desired_plan.' plan purchase']);
        }catch(\Exception $e)
        {
            $response['error']              = 1;
            $response['error_msg']          = 'Error purchasing: '.$e->getMessage();

            return $response;
        }

        if ( ! $charge->outcome->type === 'authorized')
        {
            $response['error']              = 1;
            $response['error_msg']          = 'Purchase not authorized';

            return $response;
        }

        // Now if it's the first purchase we have to obtain a phone number
        if (is_null($page->twilio_number))
        {
            // Whe have to request a new one
            $sid = env('TWILIO_SID');
            $token = env('TWILIO_AUTH_TOKEN');

            // Instantiate a new client
            $twilio = new Client($sid, $token);

            //$local = $twilio->availablePhoneNumbers("US")->local->read(["areaCode" => 813], 1);
            $local = $twilio->availablePhoneNumbers("US")->tollFree->read([], 1);
            $local_phone = $local[0];
            $number = $local_phone->phoneNumber;

            // buy the phone number
            $incoming_phone_number = $twilio->incomingPhoneNumbers->create(["phoneNumber" => $number]);

            // Update the page twilio phone number
            $page->twilio_number = $number;
            $page->save();

        }
        else{
            $number = $page->twilio_number;
        }

        $balance = $page->sms_balance;
        if ( ! isset($balance) )
        {
            $balance                            = new SmsBalance;
            $balance->total                     = $amount;;
            $balance->chatmatic_user_uid        = $user->uid;
            $balance->page_uid                  = $page_uid;
            $balance->autorenew                 = $request->get('autorenew');

            $new_balance_created = $balance->save();

            if ( ! isset($new_balance_created) )
            {
                $response['error']        = 1;
                $resposne['error_msg']    = 'Sms account could not be created.';

                return $resposne;
            }
        }
        else
        {
            //Let's update the balance
            $balance->total += $amount;
            $balance->autorenew = $request->get('autorenew');
            $balance->save();    
        }

        // Let's write the purchase on database
        $purchase                             = new \App\StripePurchase;
        $purchase->type                       = 'sms '.$desired_plan.' plan';
        $purchase->total                      = $amount;
        $purchase->chatmatic_buyer_uid        = $user->uid;
        $purchase->page_uid                   = $page_uid;
        $purchase->created_at_utc             = gmdate("Y-m-d\TH:i:s\Z");

        $purchase->save()
;
        // Get billing details from customer object
        $source = $stripe_customer_object->sources->retrieve($payment_source);
        $card = $source->card;

        // Mail telling the purchase
 
        $response['success']            = 1;
        $response['phone_number']       = $number;
        $response['billing_info']       = [
                'name'          => $desired_plan,
                'email'         => $user->facebook_email,
                'card_number'   => 'xxxx-xxxx-xxxx-'.$card->last4,
                'card_exp'      => $card->exp_month.'/'.$card->exp_year,
                'price'         => $amount
            ];

        return $response;

    }

    
}
