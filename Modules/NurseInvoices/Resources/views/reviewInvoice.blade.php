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
                {!! Form::open(['url' => route('care.center.invoice.dispute')]) !!}

                <input type="hidden" id="invoiceId" name="invoiceId" value="{{$invoiceId}}">

                <div class="form-group">
                    <label for="dispute"><h3>Dispute Invoice</h3></label>
                    <textarea class="form-control" id="dispute" name="reason"
                              placeholder="Type reasons for dispute here" rows="8" required></textarea>
                </div>

                <div class="form-group text-right">
                    <button id="submit" class="btn btn-danger">
                        Dispute Invoice
                    </button>
                </div>

                {!! Form::close() !!}
            </div>
        </div>
    </div>
@endsection
