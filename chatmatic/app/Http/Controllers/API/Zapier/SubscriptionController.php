<?php

namespace App\Http\Controllers\API\Zapier;

use App\Http\Controllers\Controller;
use App\Page;
use App\ZapierWebhookSubscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public $page;

    public function __construct(Request $request)
    {
        $page_fb_id = $request->get('page_id');
        $page       = Page::where('fb_id', $page_fb_id)->first();
        $this->page = $page;
    }

    /**
     * Creating a new subscription for Zapier's webhook system
     *
     * @param Request $request
     * @throws \Exception
     * @return array
     */
    public function create(Request $request)
    {
        // Create a new webhook subscription
        $target_url = $request->get('hookUrl');
        $action     = $request->get('action');

        $webhook_subscription['page_uid']   = $this->page->uid;
        $webhook_subscription['target_url'] = $target_url;
        $webhook_subscription['action']     = $action;

        // Create the webhook subscription
        $zap_sub = ZapierWebhookSubscription::create($webhook_subscription);

        // Whatever we return here will be included in the bundle.subscribeData object on the performUnsubscribe action
        // so we want to make sure to return the subscriptions uid
        return [
            'id' => $zap_sub->uid
        ];
    }

    /**
     * Destroy a Zapier webhook subscription
     *
     * @param Request $request
     * @throws \Exception
     * @return false|string
     */
    public function destroy(Request $request)
    {
        // Delete a webhook subscription
        $zapier_subscription_uid = $request->get('subscription_uid');

        $subscription = ZapierWebhookSubscription::find($zapier_subscription_uid);

        $subscription->delete();

        return json_encode([]);
    }

    /**
     * Return the list of updated items for the given subscription
     *
     * @param Request $request
     * @return false|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|string
     */
    public function list(Request $request)
    {
        // Perform a 'list' operation on a given subscription to get the new items
        // Need to obtain the subscription for this combination
        $action         = $request->get('action');

        // TODO: What happens if a page doesn't have any subscribers yet? We need some sample/fake data?
        $subscribers = $this->page->subscribers()->orderBy('uid', 'desc')->whereNotNull('first_name')->take(5)->get();

        $return = [];
        foreach($subscribers as $subscriber)
        {
            $return[] = (object) $subscriber->cleanDataForZapier();
        }

        return json_encode($return);
    }
}
