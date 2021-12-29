@extends('layouts.app')

@section('title', 'Comments - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/pages">Pages</a></li>
                    <li class="breadcrumb-item"><a href="/page/{!! $page->uid !!}">{!! $page->fb_name !!}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Comments</li>
                </ol>
            </nav>
            <h3>Comments</h3>
        </div>
    </div>
    <div class="row">
        <div class="col">
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
                @foreach($comments as $comment)
                    <tr>
                        <td>{!! $comment->post->uid !!}</td>
                        <td class="text-right">
                            Sent by: {!! $comment->facebook_sender_name !!}<br>
                            Sender ID: {!! $comment->facebook_sender_id !!}
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
            <nav aria-label="Pagination">
                {!! $comments->links() !!}
            </nav>
        </div>
    </div>
@endsection
