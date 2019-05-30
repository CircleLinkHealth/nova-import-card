@extends('partials.providerUI')

@section('title', 'reviewInvoice')
@section('activity', 'reviewInvoice')

@section('content')
    <div class="container" style="padding-bottom: 10%;">
        <div class="row">
            <div class="col-md-12">
                @empty($invoice->id)
                    <div class="alert alert-default" style="margin-top: 50px;">
                        <h4>Your invoice is not ready yet. You will receive an email once it has been generated.</h4>
                    </div>
                @else
                    @include('nurseinvoices::invoice-v2')
                @endempty
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
                    @elseif ($invoice->is_nurse_approved)
                        <div class="row">
                            <div class="col-md-12 alert alert-success">
                                <h4>You approved this invoice on {{presentDate($invoice->nurse_approved_at, true, true, true)}}</h4>
                            </div>
                        </div>
                    @endif
                @endisset
            </div>
        </div>
    </div>
@endsection
