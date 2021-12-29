@extends('layouts.app')

@section('title', 'Pages - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/pages">Pages</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Search: {!! request()->get('search') !!}</li>
                </ol>
            </nav>
            <div class="row">
                <div class="col-9"><h3>Search Results: {!! request()->get('search') !!}</h3></div>
                <div class="col-3 text-right">
                    <form action="/pages" class="form-inline">
                        <label class="sr-only" for="search_value">Name</label>
                        <input type="text" class="form-control mb-2 mr-sm-2" id="search_value" name="search" placeholder="Search...">
                        <button type="submit" class="btn btn-primary mb-2">Search</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            @include('pages.partials.pages-table', ['pages' => $pages, 'show_user' => true, 'search' => true])

            <nav aria-label="Pagination">
                @if(request()->has('search'))
                    @php
                        $appends['search'] = request()->get('search');
                    @endphp
                @endif

                @if(isset($appends))
                    {!! $pages->appends($appends)->links() !!}
                @else
                    {!! $pages->links() !!}
                @endif
            </nav>
        </div>
    </div>
@endsection