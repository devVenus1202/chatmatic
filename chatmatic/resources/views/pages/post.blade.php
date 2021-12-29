@extends('layouts.app')

@section('title', 'Post Detail - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/pages">Pages</a></li>
                    <li class="breadcrumb-item"><a href="/page/{!! $page->uid !!}">{!! $page->fb_name !!}</a></li>
                    <li class="breadcrumb-item"><a href="/page/{!! $page->uid !!}/posts">Posts</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Post Detail</li>
                </ol>
            </nav>
            <h3>Post Detail</h3>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    Post Details
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                        <tr>
                            <td><strong>Facebook Post ID</strong></td>
                            <td class="text-right">{!! $post->facebook_post_id !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Facebook Post Object ID</strong></td>
                            <td class="text-right">{!! $post->facebook_post_object_id !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Total Comments</strong></td>
                            <td class="text-right">{!! $post->comments !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Actionable Comments</strong></td>
                            <td class="text-right">{!! $post->comments()->count() !!}</td>
                        </tr>
                        </tbody>
                    </table>
                    @if($post->permalink_url !== null)
                        <strong>Permalink</strong><br>
                        <small><a href="{!! $post->permalink_url !!}">{!! $post->permalink_url !!}</a></small>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    Post Body
                </div>
                <div class="card-body">
                    <p>{!! $post->message !!}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row pt-4">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    Comments
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                        <th>Post</th>
                        <th class="text-right">Facebook Data</th>
                        <th>Message</th>
                        <th>Trigger</th>
                        <th>Response</th>
                        <th>Created</th>
                        </thead>
                        <tbody>
                        @foreach($post->comments()->orderBy('created_at_utc', 'desc')->get() as $comment)
                            <tr>
                                <td>{!! $comment->post->uid !!}</td>
                                <td>
                                    <div class="row">
                                        <div class="col"><strong>Sent by:</strong></div>
                                        <div class="col text-right">{!! $comment->facebook_sender_name !!}</div>
                                    </div>
                                    <div class="row">
                                        <div class="col"><strong>Sender ID:</strong></div>
                                        <div class="col text-right">{!! $comment->facebook_sender_id !!}</div>
                                    </div>
                                </td>
                                <td>{!! $comment->message !!}</td>
                                <td>@if($comment->trigger)
                                        <a href="/page/{!! $page->uid !!}/triggers">{!! $comment->trigger->uid !!}</a>
                                    @else
                                        None
                                    @endif
                                </td>
                                <td>{!! $comment->response !!}</td>
                                <td class="text-right">{!! $comment->created_at_utc->diffForHumans() !!}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
