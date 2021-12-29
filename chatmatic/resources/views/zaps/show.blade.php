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
                            @if($template->archived !== true)
                                <a href="/template/{!! $template->uid !!}/archive" class="btn btn-warning btn-sm">Archive</a>
                            @else
                                <a href="/template/{!! $template->uid !!}/un-archive" class="btn btn-success btn-sm">Un-Archive</a>
                            @endif
                                <a href="#" id="push-template-to-page" data-template-uid="{!! $template->uid !!}" class="btn btn-primary btn-sm">Push to Page</a>
                            <a href="/template/{!! $template->uid !!}/delete" class="btn btn-danger btn-sm">Delete</a>
                        </div>
                    </span>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                        <tr>
                            <td><strong>Name</strong></td>
                            <td class="text-right">{!! $template->name !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Workflow Type</strong></td>
                            <td class="text-right">{!! $template->workflow_type !!}</td>
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
                        <tr>
                            <td><strong>Updated</strong></td>
                            <td class="text-right">
                                {!! $template->updated_at_utc->diffForHumans() !!}<br>
                                {!! $template->updated_at_utc!!}<br>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-6">
            @if(mb_strlen($template->keywords) > 0)
            <div class="card">
                <div class="card-header">
                    Workflow Keywords
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                        @foreach(explode(',', $template->keywords) as $keyword)
                            <tr>
                                <td>{!! $keyword !!}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="row py-4">
        <div class="col">
            <h3>Workflow Template Steps</h3>
            <table class="table table-sm">
                <thead>
                <th>UID</th>
                <th>Name</th>
                <th>Type</th>
                <th class="text-right">Steps</th>
                <th>Parameters</th>
                </thead>
                <tbody>
                @foreach($template->workflowTemplateSteps()->orderBy('uid', 'asc')->get() as $step)
                    <tr class="table-info">
                        <td>{!! $step->uid !!}</td>
                        <td>{!! $step->name !!}</td>
                        <td>{!! $step->step_type !!}</td>
                        <td class="text-right">{!! $step->workflowTemplateStepItems->count() !!}</td>
                        <td>{!! $step->step_type_parameters !!}</td>
                    </tr>
                    @if($step->workflowTemplateStepItems->count())
                        <tr>
                            <td class="table-secondary">&nbsp;</td>
                            <td colspan="4">
                                <strong>Workflow Steps ({!! $step->name !!})</strong>
                            </td>
                        </tr>
                        <tr>
                            <th class="table-secondary"></th>
                            <th>Type</th>
                            <th>Headline</th>
                            <th>Content</th>
                            <th>Text Message</th>
                        </tr>
                        @foreach($step->workflowTemplateStepItems()->orderBy('uid', 'asc')->get() as $step_item)
                            <tr>
                                <td class="table-secondary"></td>
                                <td><strong>{!! ucfirst($step_item->item_type) !!}</strong></td>
                                <td>{!! $step_item->headline !!}</td>
                                <td>{!! $step_item->content !!}</td>
                                <td>{!! $step_item->text_message !!}</td>
                            </tr>
                            {{-- Workflow Step Item Maps --}}
                            @if($step_item->workflowTemplateStepItemMaps->count() > 0)
                                <tr>
                                    <td colspan="2" class="table-secondary"></td>
                                    <td colspan="3"><strong>Buttons</strong></td>
                                </tr>
                                <tr>
                                    <th colspan="2" class="table-secondary"></th>
                                    <th>Order</th>
                                    <th>Button Text</th>
                                    <th>Button Action / Text</th>
                                </tr>
                                @foreach($step_item->workflowTemplateStepItemMaps as $step_item_map)
                                <tr>
                                    <td colspan="2" class="table-secondary"></td>
                                    <td>{!! $step_item_map->map_order !!}</td>
                                    <td>{!! $step_item_map->map_text !!}</td>
                                    <td>
                                        {!! $step_item_map->map_action !!}
                                        <br>
                                        @if($step_item_map->map_action == 'web_url')
                                            <a href="{!! $step_item_map->map_action_text !!}" target="_blank">{!! $step_item_map->map_action_text !!}</a>
                                        @else
                                            {!! $step_item_map->map_action_text !!}
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <!--
                                <tr>
                                    <td colspan="2" class="table-secondary"></td>
                                    <td colspan="3"><strong>No buttons</strong></td>
                                </tr>
                                -->
                            @endif
                            {{-- Workflow Step Item Images --}}
                            @if($step_item->workflowTemplateStepItemImages->count() > 0)
                                <tr>
                                    <td colspan="2" class="table-secondary"></td>
                                    <td colspan="3"><strong>Images</strong></td>
                                </tr>
                                <tr>
                                    <th colspan="2" class="table-secondary"></th>
                                    <th>Order</th>
                                    <th>Image URL / Image Redirect URL</th>
                                    <th>Title / Subtitle</th>
                                </tr>
                                @foreach($step_item->workflowTemplateStepItemImages as $step_item_image)
                                    <tr>
                                        <td colspan="2" class="table-secondary"></td>
                                        <td>{!! $step_item_image->image_order !!}</td>
                                        <td>
                                            <img class="img-fluid img-thumbnail" style="max-width: 250px" src="{!! $step_item_image->image_url !!}" alt="" /><br>
                                            Image URL: <a href="{!! $step_item_image->image_url !!}" target="_blank">{!! $step_item_image->image_url !!}</a>
                                            @if($step_item_image->redirect_url)
                                                <br>Redirect URL: <a href="{!! $step_item_image->redirect_url !!}" target="_blank">{!! $step_item_image->redirect_url !!}</a>
                                            @endif
                                        </td>
                                        <td>
                                            {!! $step_item_image->image_title !!}<br>
                                            {!! $step_item_image->image_subtitle !!}
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <!--
                                <tr>
                                    <td colspan="2" class="table-secondary"></td>
                                    <td colspan="3"><strong>No images!</strong></td>
                                </tr>
                                -->
                            @endif
                        @endforeach
                        <tr class="table-secondary">
                            <td>&nbsp;</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection
