@extends('layouts.app')

@section('title', 'Feed Updates - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Updates feed</li>
                </ol>
            </nav>
            <h3>Updates</h3>
        </div>
    </div>
    <div class="row">
                <div class="col text-right">
                    <a href="feed_updates/new" class="btn btn-primary btn-sm">Add Update</a>
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
                @foreach($updates as $update)
                    <tr>
                        <td class="text-center">{!! $update->uid !!}</td>
                        <td class="text-left">{!! \Illuminate\Support\Str::limit($update->content, 150, $end='...') !!}</td>
                        <td><a href="/feed_updates/{!! $update->uid !!}/update">!</a></td>
                        <td><a href="/feed_updates/{!! $update->uid !!}/delete">x</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
