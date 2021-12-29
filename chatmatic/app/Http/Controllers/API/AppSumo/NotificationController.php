<?php

namespace App\Http\Controllers\API\Appsumo;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\AppSumoUser;

class NotificationController extends Controller
{
    /**
     * @param Request $request 
     * @param $page_uid
     * @return mixed
     */
    public function index(Request $request)
    {


        $action             = $request->get('action');

        switch ($action) {
            case 'activate':
                $plan_id            = $request->get('plan_id');
                $license_uuid       = $request->get('uuid');
                $activation_email   = $request->get('activation_email');
                $invoice_item_uuid  = $request->get('invoice_item_uuid');

                //find out if this user already exist
                $sumo_user = AppSumoUser::where('uuid', $license_uuid)->first();
                if ($sumo_user){
                    $response = [
                        'error'                 => 1,
                        'message'               => 'This license is already created'
                    ];

                    return $response;
                }

                $sumo_user = new AppSumoUser();
                $sumo_user->email               = $activation_email;
                $sumo_user->plan_id             = $plan_id;
                $sumo_user->uuid                = $license_uuid;
                $sumo_user->invoice_item_uuid   = $invoice_item_uuid;
                $sumo_user->used_licenses       = 0;
                $sumo_user->cloned_templates    = 0;
                $sumo_user->created_at_utc      = Carbon::now()->toDateTimeString();
                $sumo_user->updated_at_utc      = Carbon::now()->toDateTimeString();

                $activation = $sumo_user->save();

                // encript the sumo uid
                $encripted_sumo_uid = encrypt($sumo_user->uid);

                if($activation){
                    $response = [
                        'message'               => 'Product activated',
                        'redirect_url'          => env('ENV_URL').'/login_appsumo?id='.$encripted_sumo_uid.'&plan_id='.$plan_id,
                    ];
                }
                else{
                    $response = [
                        'error'               => 1,
                        'message'             => 'App sumo activate not stored on database'
                    ];

                    return $response;
                }
                //return $response;
                return response()->json($response,201);

            case 'enhance_tier':
                $plan_id            = $request->get('plan_id');
                $license_uuid       = $request->get('uuid');
                $activation_email   = $request->get('activation_email');

                $sumo_user = AppSumoUser::where('uuid', $license_uuid)->first();

                if($sumo_user){
                    $sumo_user->plan_id             = $plan_id;
                    $sumo_user->updated_at_utc      = Carbon::now()->toDateTimeString();
                    $updated = $sumo_user->save();

                    if($updated){

                        $response = [
                            'message' => "product enhanced",
                        ];

                        return $response;

                    }else{
                        $response = [
                            'error'             => 1,
                            'message'           => 'Tier not updated'
                        ];

                        return $response;
                    }

                }else{
                    $response = [
                        'error'             => 1,
                        'message'           => 'No user found on chatmtic app sumo users'
                    ];

                    return $response;
                }
                break;

            case 'reduce_tier':
                $plan_id            = $request->get('plan_id');
                $license_uuid       = $request->get('uuid');
                $activation_email   = $request->get('activation_email');

                $sumo_user = AppSumoUser::where('uuid', $license_uuid)->first();


                if($sumo_user){
                    $sumo_user->plan_id             = $plan_id;
                    $sumo_user->updated_at_utc      = Carbon::now()->toDateTimeString();
                    $updated = $sumo_user->save();


                    // Remove licenses according to the plan
                    $appsumo_licenses = $sumo_user->sumoLicensedPages;

                    if ($appsumo_licenses->count() > 0){

                        switch ($plan_id) {
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
                        }

                        $active_licenses = $appsumo_licenses->all();
                        $active_licenses_number = $appsumo_licenses->count();

                        while ( $active_licenses_number > $available_licenses ) {
                            // Find out the last license
                            $last_license = end($active_licenses);

                            // remove it
                            $last_license->delete();

                            // decrease the counter
                            $active_licenses_number -= 1;
                        }
                        $sumo_user->used_licenses = $active_licenses_number;
                        $sumo_user->save();
                    }


                    if($updated){

                        $response = [
                            'message' => "product reduced",
                        ];

                        return $response;

                    }else{
                        $response = [
                            'error'             => 1,
                            'message'           => 'Tier not updated on database'
                        ];

                        return $response;
                    }

                }else{
                    $response = [
                        'error'             => 1,
                        'message'           => 'No user found on chatmtic app sumo users'
                    ];

                    return $response;
                }
                break;


            case 'refund':
                $plan_id            = $request->get('plan_id');
                $license_uuid       = $request->get('uuid');
                $activation_email   = $request->get('activation_email');
                $invoice_item_uuid   = $request->get('invoice_item_uuid');

                $sumo_user = AppSumoUser::where('uuid', $license_uuid)->first();

                if($sumo_user){
                    $sumo_user->refunded            = true;
                    $sumo_user->updated_at_utc      = Carbon::now()->toDateTimeString();
                    $updated = $sumo_user->save();

                    if($updated){

                        // Remove all licenses
                        $sumo_user->sumoLicensedPages()->delete();

                        $response = [
                            'message' => "product refunded",
                        ];

                        return $response;

                    }else{
                        $response = [
                            'error'             => 1,
                            'message'           => 'Tier not updated'
                        ];

                        return $response;
                    }

                }else{
                    $response = [
                        'error'             => 1,
                        'message'           => 'No user found on chatmtic app sumo users'
                    ];

                    return $response;
                }

        }
    }
}