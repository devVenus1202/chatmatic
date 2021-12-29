<?php

namespace App\Http\Controllers\API;

use App\PersistentMenuItem;
use Illuminate\Http\Request;

class PersistentMenuController extends BaseController
{

    /**
     * @param Request $request
     * @param $page_uid
     * @return array
     */
    public function index(Request $request, $page_uid)
    {
        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        $menu = $page->persistentMenus()->where('locale', 'default')->first();
        if( ! $menu)
        {
            $menu = $page->generateDefaultPersistentMenu();
        }

        $response['menus']  = [];
        // Check here if they have a license, if not, make sure that we're including the branded menu item
        if($page->isLicensed())
        {
            $menu_items         = $menu->menuItems()
                ->whereNull('parent_menu_uid')
                ->where('branded', 0) // ensure that we don't get any branded menu items
                ->orderBy('uid', 'asc')
                ->get();
        }
        else
        {
            // Get the branded menu item as a single menu item
            $branded_menu_item  = $menu->menuItems()
                ->where('branded', 1)
                ->first();

            // Check here if the branded menu item exists, if not, create it
            if( ! $branded_menu_item)
            {
                // No branded menu item exists, let's create one
                $branded_menu_item = $menu->menuItems()->create([
                    'type'              => 'link',
                    'title'             => 'Powered by Chatmatic',
                    'payload'           => 'https://chatmatic.com?uid='.$page->fb_id,
                    'branded'           => 1,
                ]);
            }

            // Get the non-branded menu items
            $non_branded_menu_items = $menu->menuItems()
                ->whereNull('parent_menu_uid')
                ->where('branded', 0) // ensure that we don't get any branded menu items
                ->orderBy('uid', 'asc')
                ->get();

            // Combine the two into a new collection
            $menu_items[] = $branded_menu_item;

            foreach($non_branded_menu_items as $non_branded_menu_item)
            {
                // Confirm not more than 3
                if(count($menu_items) < 3)
                    $menu_items[] = $non_branded_menu_item;
            }

            $menu_items = collect($menu_items);
        }

        foreach($menu_items as $menu_item)
        {
            $payload = $menu_item->payload;

            // If it's a submenu get the children
            if($menu_item->type === 'submenu')
            {
                $sub_menu_items = $menu->menuItems()->where('parent_menu_uid', $menu_item->uid)->orderBy('uid', 'asc')->get();
                $payload = [];
                foreach($sub_menu_items as $sub_menu_item)
                {
                    $payload[] = [
                        'uid'       => $sub_menu_item->uid,
                        'page_uid'  => $page_uid,
                        'name'      => $sub_menu_item->title,
                        'type'      => $sub_menu_item->type,
                        'value'     => $sub_menu_item->payload
                    ];
                }
            }

            $branded = false;
            if($menu_item->branded)
                $branded = true;

            $response['menus'][] = [
                'uid'               => $menu_item->uid,
                'page_uid'          => $page->uid,
                'name'              => $menu_item->title,
                'type'              => $menu_item->type,
                'value'             => $payload,
                'branded'           => $branded,
            ];
        }

        return $response;
    }

    /**
     * @param Request $request
     * @param $page_uid
     * @return array
     */
    public function create(Request $request, $page_uid)
    {
        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        /** @var \App\PersistentMenu $menu */
        // Get the base/default menu parent
        $menu = $page->persistentMenus()->first();
        if( ! $menu) // In case somehow someone got here without the default, we'll check for that
        {
            $menu = $page->generateDefaultPersistentMenu();
        }

        // TODO: Validate these values
        $type                   = $request->get('type');
        $payload                = $request->get('value');
        $title                  = $request->get('name');

        $temp_menu_item['type']                 = $type;
        $temp_menu_item['title']                = $title;

        $parent_menu_item_uid = null;
        if($type === 'submenu')
        {
            // Create the base level menu item
            $menu_item                          = $menu->menuItems()->create($temp_menu_item);

            // Create the sub-menu items
            $parent_menu_item_uid               = $menu_item->uid;
            foreach($request->get('value') as $sub_menu_item_array)
            {
                $sub_menu_item = $menu->menuItems()->create([
                    'type'              => $sub_menu_item_array['type'],
                    'title'             => $sub_menu_item_array['name'],
                    'payload'           => $sub_menu_item_array['value'],
                    'parent_menu_uid'   => $parent_menu_item_uid,
                ]);
            }
        }
        else
        {
            $temp_menu_item['payload']  = $payload;
            $menu_item                  = $menu->menuItems()->create($temp_menu_item);
        }

        // Update the menu w/ facebook
        $page->updatePersistentMenu();

        return ['uid' => $menu_item->uid];
    }

    /**
     * @param Request $request
     * @param $page_uid
     * @param $menu_item_uid
     * @return array
     */
    public function update(Request $request, $page_uid, $menu_item_uid)
    {
        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        // Get the base/default menu parent
        $menu = $page->persistentMenus()->first();
        if( ! $menu) // In case somehow someone got here without the default, we'll check for that
        {
            $menu = $page->generateDefaultPersistentMenu();
        }

        $menu_item = $menu->menuItems()->where('uid', $menu_item_uid)->first();

        $payload = $request->get('value');
        if($request->get('type') === 'link')
        {
            if(mb_strlen($payload) > 255)
            {
                return [
                    'error'     => 1,
                    'error_msg' => 'Link: '.$payload.' is too long. 255 character maximum.'
                ];
            }
        }

        // TODO: Validate these fields
        $menu_item->type    = $request->get('type');
        $menu_item->payload = $payload;
        $menu_item->title   = $request->get('name');

        $parent_menu_item_uid = null;
        if($menu_item->type === 'submenu')
        {
            $parent_menu_item_uid               = $menu_item->uid;

            // Loop through existing sub menu items removing those that don't exist in $menu_item->payload
            foreach($menu->menuItems()->where('parent_menu_uid', $parent_menu_item_uid)->get() as $existing_sub_menu_item)
            {
                // Set a flag here, if this item doesn't exist in the payload we'll leave it false and delete
                $exists = false;
                // Does this $existing_sub_menu_item exist in the $menu_item->payload provided sub-menus?
                foreach($menu_item->payload as $sub_menu_item_array)
                {
                    if(isset($sub_menu_item_array['uid']) && $sub_menu_item_array['uid'] === $existing_sub_menu_item->uid)
                    {
                        $exists = true;
                    }
                }

                if( ! $exists)
                {
                    $existing_sub_menu_item->delete();
                }
            }

            // Create the sub-menu items
            foreach($menu_item->payload as $sub_menu_item_array)
            {
                // If it's an existing item, update it
                if(isset($sub_menu_item_array['uid']))
                {
                    $payload = $sub_menu_item_array['value'];
                    if($sub_menu_item_array['type'] === 'link')
                    {
                        if(mb_strlen($payload) > 255)
                        {
                            return [
                                'error'     => 1,
                                'error_msg' => 'Link: '.$payload.' is too long. 255 character maximum.'
                            ];
                        }
                    }

                    $sub_menu_item                  = $menu->menuItems()->where('uid', $sub_menu_item_array['uid'])->first();
                    $sub_menu_item->type            = $sub_menu_item_array['type'];
                    $sub_menu_item->title           = $sub_menu_item_array['name'];
                    $sub_menu_item->payload         = $payload;
                    $sub_menu_item->parent_menu_uid = $parent_menu_item_uid;
                    $sub_menu_item->save();
                }
                else // Otherwise, create a new one
                {
                    $payload = $sub_menu_item_array['value'];
                    if($sub_menu_item_array['type'] === 'link')
                    {
                        if(mb_strlen($payload) > 255)
                        {
                            return [
                                'error'     => 1,
                                'error_msg' => 'Link: '.$payload.' is too long. 255 character maximum.'
                            ];
                        }
                    }

                    $sub_menu_item = $menu->menuItems()->create([
                        'type'              => $sub_menu_item_array['type'],
                        'title'             => $sub_menu_item_array['name'],
                        'payload'           => $payload,
                        'parent_menu_uid'   => $parent_menu_item_uid,
                    ]);
                }
            }

            $menu_item->payload = null;
        }

        $menu_item->save();

        // Update the menu w/ facebook
        $page->updatePersistentMenu();

        return ['success' => true];
    }

    /**
     * @param Request $request
     * @param $page_uid
     * @param $menu_item_uid
     * @return array
     */
    public function delete(Request $request, $page_uid, $menu_item_uid)
    {
        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        // Get the base/default menu parent
        $menu = $page->persistentMenus()->first();
        if( ! $menu) // In case somehow someone got here without the default, we'll check for that
        {
            $menu = $page->generateDefaultPersistentMenu();
        }

        $menu_item = $menu->menuItems()->where('uid', $menu_item_uid)->first();

        if($menu_item)
        {
            // Determine if there are sub-menu items
            $sub_menu_items = PersistentMenuItem::where('parent_menu_uid', $menu_item->uid)->get();

            foreach($sub_menu_items as $sub_menu_item)
            {
                $sub_menu_item->delete();
            }

            $menu_item->delete();
        }

        // Update the menu w/ facebook
        $page->updatePersistentMenu();

        return ['success' => true];
    }

    /**
     * @param Request $request
     * @param $page_uid
     * @return array
     */
    public function toggleActive(Request $request, $page_uid)
    {
        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        $status = $request->get('active');
        if($status == 'true')
            $status = true;
        elseif($status == 'false')
            $status = false;
        $page->persistent_menus_active = $status;
        $page->save();

        if($page->persistent_menus_active === false)
            $page->disablePersistentMenu();
        else
            $page->updatePersistentMenu();

        return ['success' => true];
    }
}
