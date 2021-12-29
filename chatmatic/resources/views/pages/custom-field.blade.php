@extends('layouts.app')

@section('title', 'Custom Field: '.$custom_field->field_name.' - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/pages">Pages</a></li>
                    <li class="breadcrumb-item"><a href="/page/{!! $page->uid !!}">{!! $page->fb_name !!}</a></li>
                    <li class="breadcrumb-item"><a href="/page/{!! $page->uid !!}/custom-fields">Custom Fields</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{!! $custom_field->field_name !!}</li>
                </ol>
            </nav>
            <h3>Custom Field: {!! $custom_field->field_name !!}</h3>
        </div>
    </div>
    <div class="row">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    Custom Field Details
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                        <tr>
                            <td><strong>UID</strong></td>
                            <td class="text-right">{!! $custom_field->uid !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Field Name</strong></td>
                            <td class="text-right">{!! $custom_field->field_name !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Validation Type</strong></td>
                            <td class="text-right">{!! $custom_field->validation_type !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Merge tag</strong></td>
                            <td class="text-right">{!! $custom_field->merge_tag !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Type</strong></td>
                            <td class="text-right">{!! $custom_field->custom_field_type !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Default Value</strong></td>
                            <td class="text-right">{!! $custom_field->default_value !!}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    Custom Field Responses
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th>Subscriber</th>
                            <th class="text-right">Value</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($custom_field->customFieldResponses()->orderBy('uid', 'desc')->get() as $response)
                            <tr>
                                <td>
                                    <a href="/page/{!! $page->uid !!}/subscriber/{!! $response->subscriber->uid !!}">{!! $response->subscriber->email !!}</a>
                                </td>
                                <td class="text-right">{!! $response->response !!}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
