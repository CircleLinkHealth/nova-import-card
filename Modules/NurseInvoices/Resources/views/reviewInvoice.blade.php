@extends('partials.providerUI')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                @include('nurseinvoices::invoice-v2')
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <dispute-nurse-invoice invoice-id="{{$invoiceId}}"></dispute-nurse-invoice>
            </div>
        </div>
    </div>
@endsection
