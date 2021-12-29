<?php

namespace App\Http\Controllers\API\Zapier;

use App\Campaign;
use App\CustomField;
use App\Http\Controllers\Controller;
use App\ZapierEventLog;
use GuzzleHttp\Client;
use App\Subscriber;
use App\Page;
use App\Tag;
use App\ZapierWebhookSubscription;
use App\Mail\SmsPurchase;
use App\Mail\SmsRenovationIssue;
use Illuminate\Http\Request;
use Log;

class InternalController extends Controller
{

    /**
     * Internal request from pipeline indicating that a new subscriber has been added to a page
     *
     * @param Request $request
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function newSubscriber(Request $request)
    {
        $subscriber_psid    = $request->get('subscriber_psid');
        $subscriber         = Subscriber::where('user_psid', $subscriber_psid)->first();

        // TODO: Request to Fabian to include the campaign_uid here when applicable
        $campaign_uid = $request->get('campaign_uid');
        $campaign = null;
        if($campaign_uid !== null)
        {
            $campaign = Campaign::find($campaign_uid);
        }

        if( ! $subscriber)
        {
            \Log::info('++++++++++++++++++++++++++');
            \Log::error('Subscriber not found on /new-subscriber endpoint request - PSID: '.$subscriber_psid);
            \Log::info('++++++++++++++++++++++++++');

            return json_encode(['result' => 'failed', 'reason' => 'Subscriber not found']);
        }

        $page               = $subscriber->page;

        // Push new subscriber to Zapier
        $webhook_subscription = ZapierWebhookSubscription::where('page_uid', $page->uid)->where('action', 'new_subscriber')->first();

        if($webhook_subscription)
        {
            $webhook_url        = $webhook_subscription->target_url;
            $post_data          = $subscriber->cleanDataForZapier($campaign);

            $client             = new Client();
            $webhook_response   = $client->request('POST', $webhook_url, ['form_params' => $post_data]);

            // Log this zapier event
            $zapier_event       = ZapierEventLog::createPageEventRecord($page, $request, 'trigger', 'new_subscriber');
        }

        return json_encode(['result' => 'success']);

    }

    /**
     * Internal request from pipeline indicating that a new tag has been added to a subscriber
     *
     * @param Request $request
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function newTag(Request $request)
    {
        $subscriber_psid    = $request->get('subscriber_psid');
        $tag_uid            = $request->get('tag_uid');
        $subscriber         = Subscriber::where('user_psid', $subscriber_psid)->first();

        if( ! $subscriber)
        {
            return response('Subscriber not found', 500);
        }

        $tag                = Tag::find($tag_uid);

        if( ! $tag)
        {
            return response('Tag not found', 500);
        }

        $page               = $subscriber->page;

        // Push new tag/subscriber to Zapier
        $webhook_subscription = ZapierWebhookSubscription::where('page_uid', $page->uid)->where('action', 'new_tag')->first();
        if($webhook_subscription)
        {
            $webhook_url        = $webhook_subscription->target_url;
            $post_data          = $subscriber->cleanDataForZapier($tag);

            $client             = new Client();
            $webhook_response   = $client->request('POST', $webhook_url, ['form_params' => $post_data]);

            // Log this zapier event
            $zapier_event       = ZapierEventLog::createPageEventRecord($page, $request, 'trigger', 'new_tag');
        }

        return json_encode(['result' => 'success']);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updatedEmail(Request $request)
    {
        $subscriber_psid    = $request->get('subscriber_psid');
        $subscriber         = Subscriber::where('user_psid', $subscriber_psid)->first();

        if( ! $subscriber)
        {
            return response('Subscriber not found', 500);
        }

        $page               = $subscriber->page;

        // Push subscriber to Zapier
        $webhook_subscription = ZapierWebhookSubscription::where('page_uid', $page->uid)->where('action', 'email_updated')->first();
        if($webhook_subscription)
        {
            $webhook_url        = $webhook_subscription->target_url;
            $post_data          = $subscriber->cleanDataForZapier();

            $client             = new Client();
            $webhook_response   = $client->request('POST', $webhook_url, ['form_params' => $post_data]);

            // Log this zapier event
            $zapier_event       = ZapierEventLog::createPageEventRecord($page, $request, 'trigger', 'email_updated');
        }

        return json_encode(['result' => 'success']);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updatedPhone(Request $request)
    {
        $subscriber_psid    = $request->get('subscriber_psid');
        $subscriber         = Subscriber::where('user_psid', $subscriber_psid)->first();

        if( ! $subscriber)
        {
            return response('Subscriber not found', 500);
        }

        $page               = $subscriber->page;

        // Push subscriber to Zapier
        $webhook_subscription = ZapierWebhookSubscription::where('page_uid', $page->uid)->where('action', 'phone_updated')->first();
        if($webhook_subscription)
        {
            $webhook_url        = $webhook_subscription->target_url;
            $post_data          = $subscriber->cleanDataForZapier();

            $client             = new Client();
            $webhook_response   = $client->request('POST', $webhook_url, ['form_params' => $post_data]);

            // Log this zapier event
            $zapier_event       = ZapierEventLog::createPageEventRecord($page, $request, 'trigger', 'phone_updated');
        }

        return json_encode(['result' => 'success']);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updatedAttribute(Request $request)
    {
        $subscriber_psid    = $request->get('subscriber_psid');
        $custom_field_uid   = $request->get('custom_field_uid');
        $subscriber         = Subscriber::where('user_psid', $subscriber_psid)->first();
        $custom_field       = CustomField::find($custom_field_uid);

        if( ! $subscriber)
        {
            return response('Subscriber not found', 500);
        }

        if( ! $custom_field)
        {
            return response('Custom Field not found', 500);
        }

        $page               = $subscriber->page;

        // Push subscriber to Zapier
        $webhook_subscription = ZapierWebhookSubscription::where('page_uid', $page->uid)->where('action', 'attribute_updated')->first();
        if($webhook_subscription)
        {
            $webhook_url        = $webhook_subscription->target_url;
            $post_data          = $subscriber->cleanDataForZapier($custom_field);

            $client             = new Client();
            $webhook_response   = $client->request('POST', $webhook_url, ['form_params' => $post_data]);

            // Log this zapier event
            $zapier_event       = ZapierEventLog::createPageEventRecord($page, $request, 'trigger', 'attribute_updated');
        }

        return json_encode(['result' => 'success']);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function renovateSms(Request $request)
    {
        $page_uid = $request->get('page_uid');
        $page = Page::find($page_uid);
        
        if( ! $page)
        {
            return response('Page not found', 500);
        }

        // Get last purchase done for sms
        $last_purchase = $page->stripe_purchases()->orderBy('uid', 'DESC')->first();

        if( isset($last_purchase))
        {
            // We have to charge exaclty the same amount
            $user = $page->user;
            $stripe_customer_id = $user->stripe_customer_id;

            if (isset($stripe_customer_id))
            {
                $amount = $last_purchase->total;

                // Stripe customer object
                $stripe_key = \Config::get('chatmatic.services.stripe.secret');
                \Stripe\Stripe::setApiKey($stripe_key);

                $stripe_customer_object = \Stripe\Customer::retrieve($stripe_customer_id);

                $payment_source = $stripe_customer_object->default_source;

                try{
                    $charge  = \Stripe\Charge::create(['amount' => floatval($amount)*100, 
                                                   'currency' => 'usd', 
                                                   'customer' => $stripe_customer_id, 
                                                   'source' => $payment_source, 
                                                   'description' => 'Sms '.$last_purchase->type.' plan purchase']);
                }catch(\Exception $e)
                {
                    $template_mail =  new SmsRenovationIssue($user->facebook_name, $last_purchase->type, $e);
                    try{
                        Mail::to($user->facebook_email)->send($template_mail);
                    }
                    catch(\Exception $e){
                        \Log::erro('Error renovating sms plan with message: '.$e);
                    }
                }

                if ( ! $charge->outcome->type === 'authorized')
                {
                    // Let's mail the template code
                    $template_mail =  new SmsPurchase($user->facebook_name, $last_purchase->type);
                    try{
                        Mail::to($user->facebook_email)->send($template_mail);
                    }
                    catch(\Exception $e){
                        \Log::erro('Error sending notification emai after sms renovatio with message: '.$e);
                    }
                }

                // Let's write the purchase on database
                $purchase                             = new \App\StripePurchase;
                $purchase->type                       = $last_purchase->type;
                $purchase->total                      = $amount;
                $purchase->chatmatic_buyer_uid        = $user->uid;
                $purchase->page_uid                   = $page_uid;
                $purchase->created_at_utc             = gmdate("Y-m-d\TH:i:s\Z");

                $purchase->save();

            }

        }

    }
}
