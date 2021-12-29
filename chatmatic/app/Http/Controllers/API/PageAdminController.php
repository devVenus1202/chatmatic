<?php

namespace App\Http\Controllers\API;

use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Mail\PageAdminNotification;
use Illuminate\Support\Facades\Mail;

class PageAdminController extends BaseController
{

    /**
     * @param Request $request
     * @param $page_uid
     * @return array
     */
    public function index(Request $request, $page_uid)
    {
        $response   = [
            'success'   => 0,
            'error'     => 0,
            'error_msg' => '',
            'admins'    => []
        ];

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        $admins = $page->pageAdmins()->where('deleted', 0)->get();

        foreach($admins as $admin)
        {
            if($admin->user_uid !== 0)
            {
                $whole_name = $admin->user->facebook_name;
            }
            else
            {
                $whole_name = '';
            }
            $first_name = '';
            $last_name  = '';

            // If there's a space in the name we'll parse it into a first and last name
            if(mb_stristr($whole_name, ' '))
            {
                $whole_name = explode(' ', $whole_name);

                $first_name = $whole_name[0];
                $last_name  = '';
                if(isset($whole_name[1]))
                {
                    $last_name = $whole_name[1];

                    // Handle edge cases where a name might have more than one space
                    if(isset($whole_name[2]))
                    {
                        $last_name .= ' '.$whole_name[2];
                    }
                    if(isset($whole_name[3]))
                    {
                        $last_name .= ' '.$whole_name[3];
                    }
                }
            }
            elseif(mb_strlen($whole_name) > 1) // Cases where there's no space in the name we'll cast it to the first_name
            {
                $first_name = $whole_name;
            }

            if($admin->user_uid !== 0)
            {
                $photo_url = $admin->user->profilePhotoURL();
                if($photo_url['error'] === 1)
                    return $photo_url;
            }
            else
            {
                $photo_url['url'] = '';
            }

            $response['admins'][] = [
                'uid'           => $admin->uid,
                'first_name'    => $first_name,
                'last_name'     => $last_name,
                'email'         => $admin->email,
                'photo'         => $photo_url['url']
            ];
        }

        $response['success'] = 1;

        return $response;
    }

    /**
     * @param Request $request
     * @param $page_uid
     * @return array
     */
    public function create(Request $request, $page_uid)
    {
        $response   = [
            'success'   => 0,
            'error'     => 0,
            'error_msg' => '',
        ];

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        $admin_email = $request->get('email');

        $chatmatic_user = User::where('facebook_email', $admin_email)->first();

        $user_uid = 0; // TODO: This is a placeholder and should be updated when the admin user actually creates their account
        if($chatmatic_user)
            $user_uid = $chatmatic_user->uid;

        $dupe_check = $page->pageAdmins()->where('email', $admin_email)->first();
        if($dupe_check)
        {
            $response['success'] = 1;
            return $response;
        }

        // Add new admin record
        $admin = $page->pageAdmins()->create([
            'email'     => $admin_email,
            'added_by'  => $this->user->uid,
            'user_uid'  => $user_uid
        ]);

        // Save to chatmatic_user_page_map table
        $user_page_map_record = [
                    'chatmatic_user_uid'         => $user_uid,
                    'page_uid'                   => $page->uid,
                    'facebook_page_access_token' => ''
                ];
        \DB::table('chatmatic_user_page_map')->insert($user_page_map_record);


        // Let's mail the notification
        $template_mail =  new PageAdminNotification($page->name);
        Mail::to($admin_email)->send($template_mail);
        

        $response['success'] = 1;

        return $response;
    }

    /**
     * @param Request $request
     * @param $page_uid
     * @param $admin_uid
     * @return array
     */
    public function delete(Request $request, $page_uid, $admin_uid)
    {
        $response   = [
            'success'   => 0,
            'error'     => 0,
            'error_msg' => '',
        ];

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        // First confirm the admin exists, otherwise throw exception
        $admin = $page->pageAdmins()->where('uid', $admin_uid)->firstOrFail();

        // Delete from chatmatic_user_page_map table
        \DB::table('chatmatic_user_page_map')->where('chatmatic_user_uid', $admin->user_uid)->where('page_uid',$page_uid)->delete();

        // Delete the admin
        $admin->delete();

        $response['success'] = 1;

        return $response;
    }
}

