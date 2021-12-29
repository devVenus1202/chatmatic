<div class="row">
    <div class="col-6">
        <div class="card">
            <div class="card-header">
                Event Details
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tbody>
                    <tr>
                        <td><strong>Type</strong></td>
                        <td class="text-right">{!! $event->event_type !!}</td>
                    </tr>
                    <tr>
                        <td><strong>Action</strong></td>
                        <td class="text-right">{!! $event->action !!}</td>
                    </tr>
                    <tr>
                        <td><strong>Created</strong></td>
                        <td class="text-right">{!! $event->created_at_utc->diffForHumans() !!}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="card">
            <div class="card-header">
                Related Objects
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tbody>

                        @php $payload = json_decode($event->payload); @endphp

                        @if(isset($payload->subscriber_psid))

                            @php $subscriber = \App\Subscriber::where('user_psid', $payload->subscriber_psid)->first(); @endphp

                            <tr>
                                <td><strong>Subscriber</strong></td>
                                <td class="text-right">
                                    @if($subscriber)
                                        <a href="/page/{!! $page->uid !!}/subscriber/{!! $subscriber->uid !!}">{!! $subscriber->first_name !!} {!! $subscriber->last_name !!}</a>
                                    @else
                                        Not Found!
                                    @endif
                                </td>
                            </tr>
                        @endif
                        
                        @if(isset($payload->workflow_uid))

                            @php $workflow = \App\Workflow::find($payload->workflow_uid); @endphp

                            <tr>
                                <td><strong>Workflow</strong></td>
                                <td class="text-right">
                                    @if($workflow)
                                        <a href="/page/{!! $page->uid !!}/workflow/{!! $workflow->uid !!}">{!! $workflow->name !!}</a>
                                    @else
                                        Not Found!
                                    @endif
                                </td>
                            </tr>
                        @endif

                        @if(isset($payload->user_attribute_id))

                            @php $custom_field = \App\CustomField::find($payload->user_attribute_id); @endphp

                            <tr>
                                <td><strong>Custom Field</strong></td>
                                <td class="text-right">
                                    @if($custom_field)
                                        <a href="/page/{!! $page->uid !!}/custom-field/{!! $custom_field->uid !!}">{!! $custom_field->field_name !!}</a>
                                    @else
                                        Not Found!
                                    @endif
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<br>

<div class="row">
    <div class="col-6">
        <div class="card">
            <div class="card-header">
                Event Payload
            </div>
            <div class="card-body">
                <pre>{!! print_r(json_decode($event->payload), true) !!}</pre>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="card">
            <div class="card-header">
                Response Payload
            </div>
            <div class="card-body">
                <pre>{!! print_r(json_decode($event->response), true) !!}</pre>
            </div>
        </div>
    </div>
</div>