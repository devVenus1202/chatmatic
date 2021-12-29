@extends('layouts.app')

@section('title', 'Posts - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/pages">Pages</a></li>
                    <li class="breadcrumb-item"><a href="/page/{!! $page->uid !!}">{!! $page->fb_name !!}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Posts</li>
                </ol>
            </nav>
            <h3>Posts</h3>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <table class="table table-sm">
                <thead>
                <tr>
                    <th class="text-center">ID</th>
                    <th>Type</th>
                    <th>Post</th>
                    <th class="text-right">Comments</th>
                    <th class="text-right">Created</th>
                </tr>
                </thead>
                <tbody>
                @foreach($posts as $post)
                    <tr>
                        <td class="text-center"><a href="/page/{!! $page->uid !!}/post/{!! $post->uid !!}">{!! $post->uid !!}</a></td>
                        <td>{!! $post->post_type !!}</td>
                        <td>
                            @if($post->permalink_url !== null)
                                <strong>Permalink: </strong><a href="{!! $post->permalink_url !!}">{!! $post->permalink_url !!}</a><br>
                            @endif
                            <strong>Post: </strong>{!! str_limit($post->message, 250) !!}
                        </td>
                        <td class="text-right">{!! $post->comments !!} ({!! $post->comments()->count() !!})</td>
                        <td class="text-right">{!! $post->facebook_created_time_utc !!}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <nav aria-label="Pagination">
                {!! $posts->links() !!}
            </nav>
        </div>
    </div>
@endsection
