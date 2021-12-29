@extends('layouts.app')

@section('title', 'Workflows - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/templates">Workflow Templates</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Templates on Market</li>
                </ol>
            </nav>
            <h3>Templates on Market</h3>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <table class="table table-sm">
                <thead>
                <th>Name</th>
                <th>Owner</th>
                <th>Share code</th>
                <th>Category</th>
                <th>Sold</th>
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
                        <td>{!! $template->shareCode() !!}</td>
                        <td>{!! $template->category !!}</td>
                        <td>
                            <a href="/template/{!! $template->uid !!}/sold">
                                view
                            </a>
                        </td>
                        <td class="text-right">{!! $template->created_at_utc->diffForHumans() !!}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="text-center">
                {!! $templates->links() !!}
            </div>
        </div>
    </div>
@endsection
