<div class="row">
    <div class="col">
        <table class="table table-sm">
            <thead>
            <th>Flows</th>
            <th>Status</th>
            <th class="text-right">Messages Sent</th>
            <th class="text-right">Created</th>
            <th class="text-right">Scheduled At</th>
            <th class="text-right">Finished</th>
            <th></th>
            </thead>
            <tbody>
            @foreach($broadcasts as $broadcast)
                <tr class="@if($broadcast->status == 0) table-warning @elseif($broadcast->status == 3) table-success @else table-info @endif">
                    <td>
                        <a href="/page/{!! $broadcast->workflowTrigger->page->uid !!}/workflow/{!! $broadcast->workflow_trigger_uid !!}">
                            {!! $broadcast->workflowTrigger->name !!}
                        </a>
                    </td>
                    <td>{!! $broadcast->statusString() !!}</td>
                    <td class="text-right">{!! number_format($broadcast->interactions->count()) !!}
                        @if($broadcast->interactions->count())
                            <a href="/page/{!! $broadcast->workflowTrigger->page->uid !!}/broadcast/{!! $broadcast->uid !!}/messages">(view)</a>
                        @endif
                    </td>

                    <td class="text-right">{!! $broadcast->workflowTrigger->created_at_utc->diffForHumans() !!}</td>                    
                    <td class="text-right">
                        @if($broadcast->fire_at_utc !== null)
                            {!! \Carbon\Carbon::createFromTimestamp(strtotime($broadcast->fire_at_utc))->diffForHumans() !!}
                        @endif
                    </td>
                    <td class="text-right">
                        @if($broadcast->end_time_utc !== null){!! \Carbon\Carbon::createFromTimestamp(strtotime($broadcast->end_time_utc))->diffForHumans() !!}@endif
                    </td>

                    <td class="text-right">
                        <a href="/page/{!! $broadcast->workflowTrigger->page->uid !!}/broadcast/{!! $broadcast->uid !!}/messages"
                           class="btn btn-primary">Details</a>
                    </td>

                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="row">
    <div class="col">
        {!! $broadcasts->links() !!}
    </div>
</div>
