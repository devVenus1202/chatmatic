@extends('layouts.app')

@section('title', 'New Update - Chatmatic Admin')

@section('head')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-wysiwyg/0.3.3/bootstrap3-wysihtml5.min.css" rel="stylesheet" type="text/css">
@endsection

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/feed_updates">Updates feed</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Update</li>
                </ol>
            </nav>
            @if( isset($update))
                <h3>Edit</h3>
            @else
                <h3>New update</h3>
            @endif
        </div>
    </div>

    <br>

    <div class="row">
        <div class="col">
            <form method="POST">
                @csrf

                    <textarea id="editor" name="update_text" rows="10" cols="100">
                        @if(isset($update))
                            {!! $update->content !!} 
                        @endif
                    </textarea>
                <br>
                <input type="submit" value="Submit">
            </form>
        </div>
    </div>
@endsection


@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-wysiwyg/0.3.3/bootstrap3-wysihtml5.all.min.js"></script>
    <script>
        $('#editor').wysihtml5({toolbar: {fa: true}});
    </script>

    
@endsection