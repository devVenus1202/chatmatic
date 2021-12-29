@extends('layouts.app')

@section('title', 'Tips - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tips feed</li>
                </ol>
            </nav>
            <h3>Tips</h3>
        </div>
    </div>
    <div class="row">
                <div class="col text-right">
                    <a href="feed_tips/new" class="btn btn-primary btn-sm">Add Tip</a>
                </div>
    </div>

    <br>

    <div class="row">
        <div class="col">
            <table class="table table-sm">
                <thead>
                <th>Update Number</th>
                <th>Content</th>
                <th>Update</th>
                <th>Delete</th>
                </thead>
                <tbody>
                @foreach($tips as $tip)
                    <tr>
                        <td class="text-center">{!! $tip->uid !!}</td>
                        <td class="text-left">{!! \Illuminate\Support\Str::limit($tip->content, 150, $end='...') !!}</td>
                        <td><a href="/feed_tips/{!! $tip->uid !!}/update">!</a></td>
                        <td><a href="/feed_tips/{!! $tip->uid !!}/delete">x</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
