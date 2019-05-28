@extends('partials.adminUI')

<div class="container">
    @include('nurseinvoices::invoice-v2')

    {!! Form::open(array('url' => route('care.center.invoice.dispute'))) !!}

        <div class="form-group">
            <label for="dispute">Dispute Reasons</label>
            <textarea class="form-control" id="dispute" name="reason" placeholder="Type reasons for dispute here" rows="8" required></textarea>
        </div>

        <div class="form-group">
            <button id="submit" class="btn btn-primary">
                Submit
            </button>
            <input type="hidden" id="invoiceId" name="invoiceId" value="{{$invoiceId}}">
        </div>

        {!! Form::close() !!}



</div>
