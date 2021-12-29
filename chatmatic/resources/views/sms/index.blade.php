@extends('layouts.app')

@section('title', 'Sms account - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Sms accounts</li>
                </ol>
            </nav>
            <h3>Sms Accounts</h3>
        </div>
    </div>

    <br>

    <div class="row">
        <div class="col">
            <table class="table table-sm">
                <thead>
                <th>Page</th>
                <th>Phone Number</th>
                <th>Balance</th>
                <th>Autorenew</th>
                </thead>
                <tbody>
                @foreach($sms_accounts as $account)
                    <tr>
                        <td class="text-left"><a href="/page/{!! $account->page->uid !!}">{!! $account->page->fb_name !!}</a></td>
                        <td class="text-left">{!! $account->page->twilio_number !!}</td>
                        <td class="text-left">{!! $account->total !!}</td>
                        <td class="text-left">
                            @if( $account->autorenew )
                                <span class="badge badge-success">Yes</span>
                            @else
                                <span class="badge badge-danger">No</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col">

            <nav aria-label="Pagination">

                @if(isset($appends))
                    {!! $sms_accounts->appends($appends)->links() !!}
                @else
                    {!! $sms_accounts->links() !!}
                @endif
            </nav>
        </div>
    </div>
@endsection
