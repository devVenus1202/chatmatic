@extends('layouts.app')

@section('title', 'Workflow Template Detail - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/templates">Workflow Templates</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{!! $template->name !!}</li>
                </ol>
            </nav>
            <h3>Workflow Template: {!! $template->name !!}</h3>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    Template Details
                    <span class="pull-right">
                        <div class="btn-group" role="group" aria-label="Basic example">
                            @if($template->published != true)
                                <a href="/template/{!! $template->uid !!}/publish" class="btn btn-info btn-sm">Add to market</a>
                            @else
                                <a href="/template/{!! $template->uid !!}/publish" class="btn btn-warning btn-sm">Remove from market</a>
                            @endif
                            <a href="#" id="push-template-to-page" data-template-uid="{!! $template->uid !!}" class="btn btn-primary btn-sm">Push to Page</a>
                            @if($template->archived !== true)
                                <a href="/template/{!! $template->uid !!}/archive" class="btn btn-warning btn-sm">Archive</a>
                            @else
                                <a href="/template/{!! $template->uid !!}/archive" class="btn btn-success btn-sm">Un-Archive</a>
                            @endif
                        </div>
                    </span>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                        <tr>
                            <td><strong>Original Triggers</strong></td>
                            <td class="text-right">
                                @foreach($template->workflow->workflowTriggers as $trigger)
                                    {!! $trigger->type !!} 
                                @endforeach
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Root WorkflowTemplate Step UID</strong></td>
                            <td class="text-right">{!! $template->root_workflow_template_step_uid !!}</td>
                        </tr>
                        <tr>
                            <td><strong>WorkflowTemplate Steps</strong></td>
                            <td class="text-right">{!! $template->workflowTemplateSteps->count() !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Share Code</strong></td>
                            <td class="text-right">{!! $template->shareCode() !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Created</strong></td>
                            <td class="text-right">
                                {!! $template->created_at_utc->diffForHumans() !!}<br>
                                {!! $template->created_at_utc!!}<br>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    Workflow Keywords
                    <span class="pull-right">
                        <div class="btn-group" role="group" aria-label="Basic example">
                            <a href="/template/{!! $template->uid !!}/update" class="btn btn-info btn-sm">Update info</a>
                        </div>
                    </span>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td><strong>Category</strong></td>
                                <td class="text-right">{!! $template->category !!}</td>
                            </tr>
                            <tr>
                                <td><strong>Description</strong></td>
                                <td class="text-right">{!! $template->description !!}</td>
                            </tr>
                            <tr>
                                <td><strong>Price</strong></td>
                                <td class="text-right">${!! $template->price !!}</td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>
                                        <a href="{!! env('APP_UI_URL') !!}template/{!! $template->uid !!}" class="btn btn-info btn-bg" target="blank">Template preview</a>
                                    </strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


@endsection
