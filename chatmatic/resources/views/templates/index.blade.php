@extends('layouts.app')

@section('title', 'Workflows - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Workflow Templates</li>
                </ol>
            </nav>
            <h3>Workflow Templates</h3>
        </div>
    </div>
    <div class="row">

        <div>
            <a class="btn btn-success" href="/templates/market/">Purchased Templates</a>
        </div>
        &nbsp;&nbsp;
        <div>
            <a class="btn btn-info" href="/templates/on_market/">Templates on Market</a>
        </div>

        <div class="col text-right">
            <form action="/templates" class="form-inline float-right">
                <label class="sr-only" for="search_value">Template Search</label>
                <input type="text" class="form-control mb-2 mr-sm-2" id="search_value" name="search" placeholder="Template Search...">
                <button type="submit" class="btn btn-primary mb-2">Search</button>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <table class="table table-sm">
                <thead>
                <th>Name</th>
                <th>Owner</th>
                <th>Category</th>
                <th>To public</th>
                <th>Published</th>
                <th class="text-right">Steps</th>
                <th class="text-right">Created</th>
                </thead>
                <tbody>
                @foreach($templates as $template)
                    <tr class="@if($template->archived) table-warning @endif">
                        <td>
                            <a href="/template/{!! $template->uid !!}">{!! $template->name !!}</a>
                            @if($template->archived)
                                <span class="badge badge-warning">Archived</span>
                            @endif
                        </td>
                        <td>{!! $template->user->facebook_name !!}</td>
                        <td>{!! $template->category !!}</td>
                        <td>{!! $template->public !!}</td>
                        <td>{!! $template->published !!}</td>
                        <td class="text-right">{!! $template->workflowTemplateSteps->count() !!}</td>
                        <td class="text-right">{!! $template->created_at_utc->diffForHumans() !!}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="text-center">
                @if(request()->has('search'))
                    @php
                        $appends['search'] = request()->get('search');
                    @endphp
                
                @else
                    {!! $templates->links() !!}
                @endif
            </div>
        </div>
    </div>
@endsection
