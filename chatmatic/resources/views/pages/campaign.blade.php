@extends('layouts.app')

@section('title', 'Campaign Detail - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/pages">Pages</a></li>
                    <li class="breadcrumb-item"><a href="/page/{!! $page->uid !!}">{!! $page->fb_name !!}</a></li>
                    <li class="breadcrumb-item"><a href="/page/{!! $page->uid !!}/flow_triggers">Flow Trigger</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{!! $workflowTrigger->name !!}</li>
                </ol>
            </nav>
            <h3>{!! $workflowTrigger->type !!}: {!! $workflowTrigger->name !!}</h3>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="btn-group" role="group" aria-label="Basic example">
                @if($workflowTrigger->archived !== true)
                    <a href="/page/{!! $workflowTrigger->page->uid !!}/workflow_trigger/{!! $workflowTrigger->uid !!}/archive" class="btn btn-warning btn-sm">Archive</a>
                @else
                    <a href="/page/{!! $workflowTrigger->page->uid !!}/workflow_trigger/{!! $workflowTrigger->uid !!}/un-archive" class="btn btn-success btn-sm">Un-Archive</a>
                @endif
            </div>
        </div>
        <div class="col">
            <pre>
                <?php unset($workflowTrigger->updated_at_utc); ?>
                @if($workflowTrigger->type == 'broadcast')
                    {!! print_r($workflowTrigger->broadcast->toArray()) !!}
                @elseif($workflowTrigger->type == 'buttons')
                    {!! print_r($workflowTrigger->button->toArray()) !!}
                @elseif($workflowTrigger->type == 'keywordmsg')
                    {!! print_r($workflowTrigger->keyword->toArray()) !!}
                @elseif($workflowTrigger->type == 'landing_page')
                    {!! print_r($workflowTrigger->landingPage->toArray()) !!}
                @elseif($workflowTrigger->type == 'm_dot_me')
                    {!! print_r($workflowTrigger->mdotme->toArray()) !!}
                @endif
            </pre>
        </div>
    </div>
@endsection
