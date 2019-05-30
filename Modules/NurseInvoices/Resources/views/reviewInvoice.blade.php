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
                @isset($dispute)
                    <div class="col-md-12 alert alert-{{$dispute->is_resolved ? 'success' : 'warning'}}">
                        <div class="col-md-12">
                            <h3>Dispute Status: <b>{{$dispute->is_resolved ? 'Resolved' : 'Open'}}</b></h3>
                        </div>

                        <div class="col-md-12">
                            <h5>Your Message:</h5>
                        </div>
                        <div class="col-md-12">
                            <p>{{$dispute->reason}}</p>
                        </div>
                        <hr>
                        @if($dispute->is_resolved)
                            <div class="col-md-12">
                                <h5>CLH Message:</h5>
                            </div>
                            <div class="col-md-12">
                                <p>{{$dispute->resolution_note}}</p>
                                <p>written by {{optional($dispute->resolver)->getFullName()}}</p>
                            </div>
                        @endif
                    </div>
                @else
                    @if (auth()->user()->shouldShowInvoiceReviewButton())
                        <dispute-nurse-invoice invoice-id="{{$invoiceId}}"></dispute-nurse-invoice>
                    @endif
                @endisset
            </div>
        </div>
    </div>
@endsection
