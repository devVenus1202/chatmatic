@extends('layouts.app')

@section('title', 'Pipeline - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Pipeline</li>
                </ol>
            </nav>
            <div class="row">
                <div class="col-9"><h2>Pipeline Event and Queue Stats</h2></div>
                <div class="col-3 text-right">

                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    Processed/Queued/Request Events last 60 min
                </div>
                <div class="card-body">
                    {!! $processed_count_last_hour_chart->container() !!}
                    {!! $processed_count_last_hour_chart->script() !!}
                </div>
            </div>
            <br>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    Incoming Events last 60 min
                </div>
                <div class="card-body">
                    {!! $event_count_last_hour_chart->container() !!}
                    {!! $event_count_last_hour_chart->script() !!}
                </div>
            </div>
            <br>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <h4>Overall Event Counts</h4>
            <hr>
            @foreach($aggregated as $min_count => $data)

                <h5>Last @if($min_count == '1440') 24 hrs @else {!! $min_count !!} min @endif</h5>

                <table class="table table condensed">
                    <thead>

                    </thead>
                    <tbody>
                    <tr>
                        <td><strong>incoming</strong></td>
                        <td class="text-right">
                            {!! number_format($data['total_incoming']) !!}
                            @if($data['total_incoming'] > 0)
                                ({!! round($data['total_incoming'] / (int) $min_count) !!}/m)
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>queued</strong></td>
                        <td class="text-right">
                            {!! number_format($data['total_queued']) !!}
                            @if($data['total_queued'] > 0)
                                ({!! round($data['total_queued'] / (int) $min_count) !!}/m)
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>processed</strong></td>
                        <td class="text-right">
                            {!! number_format($data['total_processed']) !!}
                            @if($data['total_processed'] > 0)
                                ({!! round($data['total_processed'] / (int) $min_count) !!}/m)
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>requests</strong></td>
                        <td class="text-right">
                            {!! number_format($data['total_requests']) !!}
                            @if($data['total_requests'] > 0)
                                ({!! round($data['total_requests'] / (int) $min_count) !!}/m)
                            @endif
                        </td>
                    </tr>
                    </tbody>
                </table>
            @endforeach
        </div>
        <div class="col-md-8">
            <h4>Processed Event Breakdown</h4>
            <hr>

            <div class="accordion" id="processedAccordion">
                @foreach($aggregated as $min_count => $data)

                    <div class="card">
                        <div class="card-header" id="heading{!! $min_count !!}">
                            <h2 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse{!! $min_count !!}" aria-expanded="@if($min_count == '5') true @else false @endif" aria-controls="collapse{!! $min_count !!}">
                                    Last @if($min_count == '1440') 24 hrs @else {!! $min_count !!} min @endif
                                </button>
                            </h2>
                        </div>

                        <div id="collapse{!! $min_count !!}" class="collapse @if($min_count == '5') show @else  @endif" aria-labelledby="heading{!! $min_count !!}" data-parent="#processedAccordion">
                            <div class="card-body">
                                @foreach($data['processed_data'] as $key_name => $key_array)
                                    <div class="row">
                                        <div class="col-9"><strong>{!! $key_name !!}</strong></div>
                                        <div class="col-3">
                                            <div class="row">
                                                <div class="col-6"><strong><small>Count</small></strong></div>
                                                <div class="col-6 text-right">{!! $key_array['count'] !!}</div>
                                            </div>
                                            <div class="row">
                                                <div class="col-6"><strong><small>Time</small></strong></div>
                                                <div class="col-6 text-right">{!! round($key_array['time'], 2) !!} s</div>
                                            </div>
                                            @if($key_array['count'] > 0)
                                                <div class="row">
                                                    <div class="col-6"><strong><small>Avg</small></strong></div>
                                                    <div class="col-6 text-right">{!! round($key_array['time'] / $key_array['count'], 2) !!} s</div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col">
                                            <div style="border-top: 1px solid black;">&nbsp;</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-8">
            <h4>Last 60 min raw data</h4>
            <hr>
            <table class="table table-condensed">
                <thead>

                </thead>
                <tbody>
                @foreach($values60 as $key => $array)
                    <tr>
                        <th colspan="2">{!! $array['date'] !!}</th>
                    </tr>
                    @foreach($array as $k => $v)
                        @if($k !== 'date')
                            <tr>
                                <td><strong>{!! $k !!}</strong></td>
                                <td class="text-right">
                                    @if(! is_array($v))
                                        {!! $v !!}
                                    @else
                                        @php unset($v['raw_values']) @endphp
                                        @foreach($v as $k2 => $v2)
                                            <div class="row">
                                                <div class="col">{!! $k2 !!}</div>
                                                <div class="col text-right">
                                                    @if(is_float($v2))
                                                        {!! round($v2, 2) !!}
                                                    @else
                                                        {!! $v2 !!}
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </td>
                            </tr>
                        @endif
                    @endforeach
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection