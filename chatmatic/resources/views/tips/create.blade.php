@extends('layouts.app')

@section('title', 'New Tip - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/feed_tips">Tips feed</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tip</li>
                </ol>
            </nav>
            @if( isset($tip))
                <h3>Edit</h3>
            @else
                <h3>New Tip</h3>
            @endif
        </div>
    </div>

    <br>

    <div class="row">
        <div class="col">
            <form method="POST">
                @csrf
                    @if(isset($tip))
                        <input class="form-control" type="url" name="tip_text" value="{!! $tip->content !!}">
                    @else    
                        <input class="form-control" type="url" name="tip_text" placeholder="Enter URL">
                    @endif
                    
                <br>
                <input type="submit" value="Submit">
            </form>
        </div>
    </div>
@endsection