<?php

namespace App\Http\Controllers\Stripe;

use App\AuditLog;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function incoming(Request $request)
    {
        \Log::debug('Incoming stripe webhook');
        \Log::debug($request->all());

        $audit_log = [
            'chatmatic_user_uid'    => 0,
            'page_uid'              => 0,
            'event'                 => 'stripe.webhook',
            'message'               => json_encode($request->all(), JSON_UNESCAPED_SLASHES)
        ];
        AuditLog::create($audit_log);
    }
}
