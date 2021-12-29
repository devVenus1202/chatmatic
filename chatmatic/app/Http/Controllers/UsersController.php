<?php

namespace App\Http\Controllers;

use App\Page;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        if($request->has('orderBy'))
        {
            $users = User::OrderByRequest($request);
        }
        elseif($request->has('search'))
            $users = User::search($request->get('search'));
        else
            $users = User::orderBy('uid', 'DESC');

        $users = $users->paginate(20);

        return view('user.index')->
            with('users', $users);
    }

    public function mostPages(Request $request)
    {
        $users = User::with('pages')->get();

        foreach($users as $key => $user)
        {
            $users[$key]->pages_count = $user->pages->count();
            //$users[$key]->connected_pages_count = $user->pages()->where('is_connected', 1)->count();
        }

        $users = $users->sortByDesc('pages_count')->take(100);

        return view('user.most-pages')->
            with('users', $users);
    }

    public function mostConnectedPages(Request $request)
    {
        $users = User::with('pages')->get();

        foreach($users as $key => $user)
        {
            $users[$key]->connected_pages_count = $user->pages()->where('is_connected', 1)->count();
        }

        $users = $users->sortByDesc('connected_pages_count')->take(100);

        return view('user.most-pages')->
            with('users', $users);
    }

    public function show(Request $request, $user_id)
    {
        $user = User::find($user_id);

        $pages = $user->pages();
        if($request->has('orderBy'))
            $pages = $pages->OrderByRequest($request);
        else
            $pages = $pages->orderBy('fb_name', 'asc');
        $pages = $pages->get();

        return view('user.show')->
            with('user', $user)->
            with('pages', $pages);
    }

    public function licensing(Request $request, $user_id)
    {
        $user = User::find($user_id);

        return view('user.licensing')
            ->with('user', $user);
    }

    public function sessions(Request $request, $user_id)
    {
        $user = User::find($user_id);

        return view('user.sessions')
            ->with('user', $user);
    }

    public function referred(Request $request)
    {
        $users = User::whereNotNull('referred');

        $users = $users->paginate(20);

        return view('user.index')->
            with('users', $users);
    }

    public function appsumo(Request $request)
    {
        $users = \App\AppSumoUser::all();

        return view('user.appsumo')->
            with('users', $users);
    }

    public function appsumoLogin(Request $request, $sumo_id)
    {
        // Find out the associated plan
        $sumo_user = \App\AppSumoUser::find($sumo_id);
        $encripted_sumo_uid = encrypt($sumo_user->uid);

        $login_url = env('ENV_URL').'/login_appsumo?id='.$encripted_sumo_uid.'&plan_id='.$sumo_user->plan_id;

        return view('user.appsumologin')->
            with('login_url', $login_url);
    }
}
