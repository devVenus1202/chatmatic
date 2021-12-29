@extends('layouts.app')

@section('title', 'Custom Fields - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/pages">Pages</a></li>
                    <li class="breadcrumb-item"><a href="/page/{!! $page->uid !!}">{!! $page->fb_name !!}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Custom Fields</li>
                </ol>
            </nav>
            <h3>Custom Fields</h3>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <table class="table table-sm">
                <thead>
                <tr>
                    <th class="text-center">UID</th>
                    <th>Name</th>
                    <th>Validation Type</th>
                    <th>Merge Tag</th>
                    <th class="text-center">Type</th>
                    <th>Default Value</th>
                    <th class="text-center">Responses</th>
                </tr>
                </thead>
                <tbody>
                @foreach($custom_fields as $custom_field)
                    <tr>
                        <td class="text-center">{!! $custom_field->uid !!}</td>
                        <td>
                            <a href="/page/{!! $page->uid !!}/custom-field/{!! $custom_field->uid !!}">{!! $custom_field->field_name !!}</a>
                        </td>
                        <td>{!! $custom_field->validation_type !!}</td>
                        <td>{!! $custom_field->merge_tag !!}</td>
                        <td class="text-center">{!! $custom_field->custom_field_type !!}</td>
                        <td>{!! $custom_field->default_value !!}</td>
                        <td class="text-center">
                            <a href="/page/{!! $page->uid !!}/custom-field/{!! $custom_field->uid !!}">{!! number_format($custom_field->customFieldResponses()->count()) !!}</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <nav aria-label="Pagination">
                {!! $custom_fields->links() !!}
            </nav>
        </div>
    </div>
@endsection
