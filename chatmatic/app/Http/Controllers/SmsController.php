<?php

namespace App\Http\Controllers;

use App\ChatmaticFeedTip;
use Illuminate\Http\Request;

class SmsController extends Controller
{

    public function index(Request $request)
    {
        $sms_accounts             = \App\SmsBalance::paginate(20);

        return view('sms.index')
            ->with('sms_accounts', $sms_accounts);
    }

}