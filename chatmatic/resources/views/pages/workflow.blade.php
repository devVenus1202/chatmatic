@extends('layouts.app')

@section('title', 'Workflow Detail - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/pages">Pages</a></li>
                    <li class="breadcrumb-item"><a href="/page/{!! $page->uid !!}">{!! $page->fb_name !!}</a></li>
                    <li class="breadcrumb-item"><a href="/page/{!! $page->uid !!}/workflows">Flows</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{!! $workflow->name !!}</li>
                </ol>
            </nav>
            <h3>Flow: {!! $workflow->name !!}</h3>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    Flow Details
                    <span class="pull-right">
                        <div class="btn-group" role="group" aria-label="Basic example">
                            <a href="#" class="btn btn-info btn-sm create-workflow-template-button"
                               data-workflow-uid="{!! $workflow->uid !!}"
                               data-page-uid="{!! $page->uid !!}"
                               data-workflow-name="{!! $workflow->name !!}">Create Template</a>
                            <a href="/page/{!! $page->uid !!}/workflow/{!! $workflow->uid !!}/delete" class="btn btn-danger btn-sm">Delete</a>
                        </div>
                    </span>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                        <tr>
                            <td><strong>Name</strong></td>
                            <td class="text-right">{!! $workflow->name !!}</td>
                        </tr>
                        @if($workflow->workflow_type === 'keywordmsg')
                            <tr>
                                <td><strong>Keywords</strong></td>
                                <td class="text-right">{!! $workflow->keywords !!}</td>
                            </tr>
                            <tr>
                                <td><strong>Match Type</strong></td>
                                <td class="text-right">{!! $workflow->keywords_option !!}</td>
                            </tr>
                        @endif
                        <tr>
                            <td><strong>Root Workflow Step UID</strong></td>
                            <td class="text-right">{!! $workflow->root_workflow_step_uid !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Workflow Steps</strong></td>
                            <td class="text-right">{!! $workflow->workflowSteps->count() !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Created</strong></td>
                            <td class="text-right">
                                {!! $workflow->created_at_utc->diffForHumans() !!}<br>
                                {!! $workflow->created_at_utc!!}<br>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Updated</strong></td>
                            <td class="text-right">
                                {!! $workflow->updated_at_utc->diffForHumans() !!}<br>
                                {!! $workflow->updated_at_utc!!}<br>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row py-4">
        <div class="col">
            <h3>Flow Steps</h3>
            <table class="table table-sm">
                <thead>
                <th>UID</th>
                <th>Type</th>
                <th colspan="2">Name</th>
                <th class="text-right">Items</th>
                </thead>
                <tbody>
                @foreach($workflow->workflowSteps()->orderBy('uid', 'asc')->get() as $step)

                    @if($step->step_type == 'items')
                        <tr class="table-info">
                            <td>{!! $step->uid !!}</td>
                            <td>{!! $step->step_type !!}</td>
                            <td colspan="2">{!! $step->name !!}</td>
                            <td class="text-right">{!! $step->workflowStepItems->count() !!}</td>
                        </tr>


                        @if($step->workflowStepItems->count())
                            <tr>
                                <td class="table-secondary">&nbsp;</td>
                                <td colspan="4" class="table-warning">
                                    <strong>Flow Step Items ({!! $step->workflowStepItems->count() !!})</strong>
                                </td>
                            </tr>
                            <tr>
                                <th class="table-secondary"></th>
                                <th>Type</th>
                                <th>Headline</th>
                                <th>Content</th>
                                <th>Text Message</th>
                            </tr>
                            @foreach($step->workflowStepItems()->orderBy('uid', 'asc')->get() as $step_item)
                                <tr>
                                    <td class="table-secondary"></td>
                                    <td><span class="badge badge-primary"><strong>{!! ucfirst($step_item->item_type) !!}</strong></span></td>
                                    <td>{!! $step_item->headline !!}</td>
                                    <td>{!! $step_item->content !!}</td>
                                    <td>{!! $step_item->text_message !!}</td>
                                </tr>
                                {{-- Workflow Step Item Maps --}}
                                @if($step_item->workflowStepItemMaps->count() > 0)
                                    <tr>
                                        <td colspan="2" class="table-secondary"></td>
                                        <td colspan="3" class="table-warning"><strong>Buttons ({!! $step_item->workflowStepItemMaps->count() !!})</strong></td>
                                    </tr>
                                    <tr>
                                        <th colspan="2" class="table-secondary"></th>
                                        <th>UID</th>
                                        <th>Button Text</th>
                                        <th>Button Action / Text</th>
                                    </tr>
                                    @foreach($step_item->workflowStepItemMaps()->orderBy('uid')->get() as $step_item_map)
                                        <tr>
                                            <td colspan="2" class="table-secondary"></td>
                                            <td>{!! $step_item_map->uid !!}</td>
                                            <td>{!! $step_item_map->map_text !!}</td>
                                            <td>
                                                {!! $step_item_map->map_action !!}
                                                <br>
                                                @if($step_item_map->map_action == 'web_url')
                                                    <a href="{!! $step_item_map->map_action_text !!}" target="_blank">{!! $step_item_map->map_action_text !!}</a>
                                                @else
                                                    {!! $step_item_map->map_action_text !!}
                                                    @if($step_item_map->custom_field_uid !== null)
                                                        <br>
                                                        <span class="badge badge-primary"><strong>Custom Field </strong></span> - <strong>{!! \App\CustomField::find($step_item_map->custom_field_uid)->field_name !!}</strong>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                        @if($step_item_map->tags()->count())
                                            <tr>
                                                <td colspan="3" class="table-secondary"></td>
                                                <td colspan="2"><strong>Tags ({!! $step_item_map->tags()->count() !!})</strong></td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" class="table-secondary"></td>
                                                <td colspan="2">
                                                    @foreach($step_item_map->tags()->get() as $tag)
                                                        <span class="badge badge-primary">{!! $tag->value !!} - ({!! $tag->uid !!})</span>&nbsp;
                                                    @endforeach
                                                </td>
                                            </tr>
                                        @endif
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
                                @if($step_item->workflowStepItemImages->count() > 0)
                                    <tr>
                                        <td colspan="2" class="table-secondary"></td>
                                        <td colspan="3" class="table-warning"><strong>Images ({!! $step_item->workflowStepItemImages->count() !!})</strong></td>
                                    </tr>
                                    <tr>
                                        <th colspan="2" class="table-secondary"></th>
                                        <th colspan="2">Image URL / Image Redirect URL</th>
                                        <th>Title / Subtitle</th>
                                    </tr>
                                    @foreach($step_item->workflowStepItemImages as $step_item_image)
                                        <tr>
                                            <td colspan="2" class="table-secondary"></td>
                                            <td colspan="2">
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
                                {{-- Workflow Step Item Videos --}}
                                @if($step_item->workflowStepItemVideos->count() > 0)
                                    <tr>
                                        <td colspan="2" class="table-secondary"></td>
                                        <td colspan="3" class="table-warning"><strong>Videos ({!! $step_item->workflowStepItemVideos->count() !!})</strong></td>
                                    </tr>
                                    <tr>
                                        <th colspan="2" class="table-secondary"></th>
                                        <th colspan="3">Video URL / Video Redirect URL</th>
                                    </tr>
                                    @foreach($step_item->workflowStepItemVideos as $step_item_video)
                                        <tr>
                                            <td colspan="2" class="table-secondary"></td>
                                            <td colspan="3">
                                                Video URL: <a href="{!! $step_item_video->video_url !!}" target="_blank">{!! $step_item_video->video_url !!}</a>
                                            </td>
                                        </tr>
                                    @endforeach
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
                        @if($step->quickReplies->count())
                            <tr>
                                <td class="table-secondary">&nbsp;</td>
                                <td colspan="4" class="table-warning">
                                    <strong>Quick Replies ({!! $step->quickReplies->count() !!})</strong>
                                </td>
                            </tr>
                            <tr>
                                <th class="table-secondary"></th>
                                <th>Type</th>
                                <th>Text</th>
                                <th colspan="2">Payload</th>
                            </tr>
                            @foreach($step->quickReplies()->orderBy('uid')->get() as $quick_reply)
                                <tr>
                                    <td class="table-secondary"></td>
                                    <td><span class="badge badge-primary"><strong>{!! ucfirst($quick_reply->type) !!}</strong></span></td>
                                    <td>{!! $quick_reply->map_text !!}</td>
                                    <td>
                                        {!! $quick_reply->map_action_text !!}
                                        @if($quick_reply->custom_field_uid !== null)
                                            <br>
                                            <span class="badge badge-primary"><strong>Custom Field </strong></span> - <strong>{!! \App\CustomField::find($quick_reply->custom_field_uid)->field_name !!}</strong>
                                            @if($quick_reply->default_value !== null)
                                                 - Default Value: {!! $quick_reply->default_value !!}
                                            @else
                                                - No Default Value (Value will be what is submitted in the quick reply postback)
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                                @if($quick_reply->tags()->count())
                                    <tr>
                                        <td colspan="2" class="table-secondary"></td>
                                        <td colspan="3" class="table-warning"><strong>Tags ({!! $quick_reply->tags()->count() !!})</strong></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="table-secondary"></td>
                                        <td colspan="3">
                                            @foreach($quick_reply->tags()->get() as $tag)
                                                <span class="badge badge-primary">{!! $tag->value !!} - ({!! $tag->uid !!})</span>&nbsp;
                                            @endforeach
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        @endif

                        @if ($step->child_step_uid)
                        <tr>
                            <td class="table-secondary"></td>
                            <td><span class="badge badge-success"><strong>Next step</strong></span></td>
                            <td colspan="3">{!! $step->child_step_uid !!}</td>
                        </tr>
                        @endif

                    @elseif ($step->step_type == 'sms')
                        <tr class="table-info">
                            <td>{!! $step->uid !!}</td>
                            <td>{!! $step->step_type !!}</td>
                            <td colspan="3">{!! $step->name !!}</td>
                        </tr>

                        <tr>
                            <td class="table-secondary"></td>
                            <td><span class="badge badge-primary"><strong>sms text</strong></span></td>
                            <td colspan="2">{!! $step->optionSms->text_message !!}</td>
                            @if (isset($step->optionSms->phone_number_to))
                                <td class="text-right">Notify to: {!! $step->optionSms->phone_number_to !!}</td>
                            @endif
                        </tr>

                        @if ($step->child_step_uid)
                        <tr>
                            <td class="table-secondary"></td>
                            <td><span class="badge badge-success"><strong>Next step</strong></span></td>
                            <td colspan="3">{!! $step->child_step_uid !!}</td>
                        </tr>
                        @endif

                    @elseif ($step->step_type == 'conditions')
                        <tr class="table-info">
                            <td>{!! $step->uid !!}</td>
                            <td>{!! $step->step_type !!}</td>
                            <td colspan="3">{!! $step->name !!}</td>
                        </tr>

                            <tr>
                                <td class="table-secondary"></td>
                                <td><b>Type</b></td>
                                <td><b>Name</b></td>
                                <td><b>Conditions</b></td>
                                <td class="text-right"><b>Next step</b></td>
                            </tr>
                        @foreach ($step->optionConditions()->get() as $condition)
                            <tr>
                                <td class="table-secondary"></td>
                                <td><span class="badge badge-primary"><strong>{!! $condition->match !!}</strong></span></td>
                                <td>{!! $condition->name !!}</td>
                                <td>{!! $condition->conditions_json !!}</td>
                                <td class="text-right">next-step::{!! $condition->next_step_uid !!}</td>
                            </tr>
                        @endforeach

                    @elseif ($step->step_type == 'randomizer')
                        <tr class="table-info">
                            <td>{!! $step->uid !!}</td>
                            <td>{!! $step->step_type !!}</td>
                            <td colspan="3">{!! $step->name !!}</td>
                        </tr>

                        <tr>
                            <td class="table-secondary"></td>
                            <td><b>Name</b></td>
                            <td><b>Probability</b></td>
                            <td colspan="2" class="text-right"><b>Next step</b></td>
                        </tr>

                        @foreach ($step->optionRandomizations()->get() as $randomization)
                            <tr>
                                <td class="table-secondary"></td>
                                <td>{!! $randomization->name !!}</td>
                                <td>{!! $randomization->probability !!}</td>
                                <td colspan="2" class="text-right">next-step::{!! $randomization->next_step_uid !!}</td>
                            </tr>
                        @endforeach

                    @elseif ($step->step_type == 'delay')
                        <tr class="table-info">
                            <td>{!! $step->uid !!}</td>
                            <td>{!! $step->step_type !!}</td>
                            <td colspan="3">{!! $step->name !!}</td>
                        </tr>

                        <tr>
                            <td class="table-secondary"></td>
                            <td><b>Type</b></td>
                            <td><b>Time</b></td>
                            <td colspan="2" class="text-right"><b>Next step</b></td>
                        </tr>

                        <tr>
                            <td class="table-secondary"></td>
                            <td>{!! $step->optionDelay->type !!}</td>
                            <td>
                                @if ($step->optionDelay->type == 'remaining')
                                    {!! $step->optionDelay->amount !!}
                                    {!! $step->optionDelay->time_unit !!}
                                @else
                                    {!! $step->optionDelay->fire_until !!}
                                @endif
                            </td>
                            <td colspan="2" class="text-right">next-step::{!! $step->optionDelay->next_step_uid !!}</td>
                        </tr>
                        

                    @endif
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection
