@extends('layouts.app')

@section('title', 'Pages - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Pages</li>
                </ol>
            </nav>
            <div class="row">
                <div class="col-6"><h3>Pages</h3></div>
                <div class="col-3 text-right">
                    <a href="/pages/connected-250" class="btn btn-link">Connected w/ 250+ subs</a>
                </div>
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
            @include('pages.partials.pages-table', ['pages' => $pages, 'show_user' => true])

            <nav aria-label="Pagination">
                @if(request()->has('orderBy'))
                    @php
                        $appends['orderBy'] = request()->get('orderBy');
                        $appends['direction'] = request()->get('direction');
                    @endphp
                @endif
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
