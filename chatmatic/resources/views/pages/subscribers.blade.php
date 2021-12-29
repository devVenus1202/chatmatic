@extends('layouts.app')

@section('title', 'Subscribers - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/pages">Pages</a></li>
                    <li class="breadcrumb-item"><a href="/page/{!! $page->uid !!}">{!! $page->fb_name !!}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Subscribers</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="row pb-4">
        <div class="col">
            <h3>Subscribers ({!! number_format($page->subscribers()->count()) !!})</h3>
        </div>
        <div class="col text-right">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#psidImportModal">Import Subscribers</button>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <table class="table table-sm">
                <thead>
                <tr>
                    <th>Name</th>
                    <th class="text-right">Msgs. From</th>
                    <th class="text-right">Msgs. Sent</th>
                    <th class="text-right">Msgs. Accepted</th>
                    <th class="text-right">Msgs. Read</th>
                    <th class="text-right">Tags</th>
                    <th class="text-right">Custom Field Resp</th>
                    <th class="text-right">Created</th>
                </tr>
                </thead>
                <tbody>
                @foreach($subscribers as $subscriber)
                    <tr>
                        <td><a href="/page/{!! $page->uid !!}/subscriber/{!! $subscriber->uid !!}">{!! $subscriber->first_name !!} {!! $subscriber->last_name !!}</a></td>
                        <td class="text-right">{!! $subscriber->chatHistory()->count() !!}</td>
                        <td class="text-right">{!! $subscriber->messages_attempted_from_bot !!}</td>
                        <td class="text-right">{!! $subscriber->messages_accepted_from_bot !!}</td>
                        <td class="text-right">{!! $subscriber->messages_read !!}</td>
                        <td class="text-right">{!! $subscriber->tags()->count() !!}</td>
                        <td class="text-right">{!! $subscriber->customFieldResponses()->count() !!}</td>
                        <td class="text-right">{!! $subscriber->created_at_utc->diffForHumans() !!}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <nav aria-label="Pagination">
                {!! $subscribers->links() !!}
            </nav>
        </div>
    </div>


    <!-- PSID import modal -->
    <div class="modal fade" id="psidImportModal" tabindex="-1" role="dialog" aria-labelledby="psidImportModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="psidImportModalLabel">PSID Import</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="post" action="/page/{!! $page->uid !!}/subscribers/import">
                        @csrf
                        <div class="form-group">
                            <label for="psidInput">PSIDs</label>
                            <textarea class="form-control" id="psidInput" rows="3" name="psids"></textarea>
                            <small id="psidInputHelp" class="form-text text-muted">One PSID per line.</small>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


@endsection
