<table class="table table-sm">
    <thead>
    <th>
        Name
        @if(isset($show_user))
            <br> User
        @endif
    </th>
    <th class="text-right">Flows</th>
    <th class="text-right">Flow Triggers</th>
    <th class="text-center">Licensed</th>
    <th class="text-center">Connected
        @if(!isset($search))
        <span class="pull-right">
            <a href="?orderBy=is_connected&direction=asc"><i class="fa fa-arrow-up" aria-hidden="true"></i></a>
            <a href="?orderBy=is_connected&direction=desc"><i class="fa fa-arrow-down" aria-hidden="true"></i></a>
        </span>
        @endif
    </th>
    <th class="text-right">Subscribers
        @if(!isset($search))
        <span class="pull-right">
            <a href="?orderBy=subscribers&direction=asc"><i class="fa fa-arrow-up" aria-hidden="true"></i></a>
            <a href="?orderBy=subscribers&direction=desc"><i class="fa fa-arrow-down" aria-hidden="true"></i></a>
        </span>
        @endif
    </th>
    <th class="text-right">Most Recent Subscriber</th>
    <th class="text-right">Created
        @if(!isset($search))
        <span class="pull-right">
            <a href="?orderBy=created_at_utc&direction=asc"><i class="fa fa-arrow-up" aria-hidden="true"></i></a>
            <a href="?orderBy=created_at_utc&direction=desc"><i class="fa fa-arrow-down" aria-hidden="true"></i></a>
        </span>
        @endif
    </th>
    <th class="text-center">Token Issue</th>
    </thead>
    <tbody>
    @foreach($pages as $page)
        @php $subscribers_count = $page->subscribers()->count(); @endphp
        <tr>
            <td>
                <a href="/page/{!! $page->uid !!}">{!! $page->fb_name !!}</a>
                @if(isset($show_user))
                    <br><small><a href="/user/{!! $page->user->uid !!}">{!! $page->user->facebook_name !!}</a></small>
                @endif
            </td>
            <td class="text-right">{!! $page->workflows()->count() !!}</td>
            <td class="text-right">{!! $page->workflowTriggers->count() !!}</td>
            <td class="text-center">
                @if($page->licenses()->count() > 0)
                    <span class="badge badge-success">Yes</span>
                @elseif($subscribers_count > (config('chatmatic.max_free_subscribers')))
                    <span class="badge badge-danger">No</span>
                @else
                    <span class="badge badge-warning">No</span>
                @endif
            </td>
            <td class="text-center">
                @if($page->is_connected)
                    <span class="badge badge-success">Yes</span>
                @else
                    <span class="badge badge-danger">No</span>
                @endif
            </td>
            <td class="text-right">
                @if($subscribers_count > 0)
                    {!! number_format($subscribers_count) !!}
                @else
                @endif
            </td>
            <td class="text-right">
                @if($subscribers_count > 0)
                    <small>{!! $page->subscribers()->where('created_at_utc', '>', \Carbon\Carbon::now()->subDay())->count() !!} last 24hr</small>
                    <br>
                    <small>{!! $page->subscribers()->orderBy('uid', 'DESC')->first()->created_at_utc->diffForHumans() !!}</small>
                @endif
            </td>
            <td class="text-right">{!! $page->created_at_utc->diffForHumans() !!}</td>
            <td class="text-right">
                @if($page->token_error == 1)
                    <span class="badge badge-danger">Yes</span>
                @else
                    <span class="badge badge-success">No</span>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
