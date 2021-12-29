@extends('layouts.app')

@section('title', 'Workflows - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/pages">Pages</a></li>
                    <li class="breadcrumb-item"><a href="/page/{!! $page->uid !!}">{!! $page->fb_name !!}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Flows</li>
                </ol>
            </nav>
            <h3>Flows</h3>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <table class="table table-sm">
                <thead>
                <th>Name</th>
                <th class="text-right">Flow Triggers</th>
                <th class="text-right">Steps</th>
                <th class="text-right">Created</th>
                </thead>
                <tbody>
                @foreach($page->workflows()->orderBy('uid', 'DESC')->get() as $workflow)
                    <tr class="@if($workflow->archived) table-warning @endif">
                        <td>
                            <a href="/page/{!! $page->uid !!}/workflow/{!! $workflow->uid !!}">{!! $workflow->name !!}</a>
                            @if($workflow->archived)
                                <span class="badge badge-warning">Archived</span>
                            @endif
                        </td>
                        <td class="text-right">{!! $workflow->workflowTriggers->count() !!}</td>
                        <td class="text-right">{!! $workflow->workflowsteps->count() !!}</td>
                        <td class="text-right">{!! $workflow->created_at_utc->diffForHumans() !!}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
