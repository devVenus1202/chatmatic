@extends('layouts.app')

@section('title', 'Templates Market - Chatmatic Admin')

@section('content')
    <div class="row py-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Workflow Templates</li>
                </ol>
            </nav>
            <h3>Templates Market</h3>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <table class="table table-sm">
                <thead>
                <th>Name</th>
                <th>Seller</th>
                <th>Buyer</th>
                <th>Buyer Page</th>
                <th>Total</th>
                <th class="text-right">Date</th>
                </thead>
                <tbody>
                @foreach($purchases as $purchase)
                    <tr>
                        <td>{!! $purchase->template->name !!}</td>
                        <td>{!! $purchase->chatmatic_seller->facebook_name !!}</td>
                        <td>{!! $purchase->chatmatic_buyer->facebook_name !!}</td>
                        <td>{!! $purchase->page->fb_name !!}</td>
                        <td>{!! $purchase->total !!}</td>
                        <td class="text-right">{!! $purchase->created_at_utc !!}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col">
            {!! $purchases->links() !!}
        </div>
    </div>
@endsection
