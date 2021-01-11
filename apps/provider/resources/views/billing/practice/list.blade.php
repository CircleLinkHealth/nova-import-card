@extends('partials.providerUI')

@section('title', 'Billable Patient Reports & Invoices')
@section('activity', 'Billable Patient Reports & Invoices')

@section('content')
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <div class="container-fluid" style="padding-top: 50px;">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                {{ csrf_field() }}
                <div class="panel panel-default">
                    <div class="panel-heading">Billable Patient Reports & Invoices</div>
                    <div class="panel-body">

                        @foreach($invoices as $key => $value)
                            <li>
                                <b>{{$key}}</b>
                                <a href="{{$value['invoice_url']}}">Invoice</a> •
                                <a href="{{$value['patient_report_url']}}">Patient Report</a> •
                            </li>
                        @endforeach

                        <hr>

                        <div class="row" style="padding-left: 30px;">
                            {!! Form::open(array('url' => route('monthly.billing.send', array()),'class' => 'form-horizontal')) !!}
                                <input type="hidden" value="{{json_encode($invoices)}}" name="links">
                                <button id="submit" name="send" class="btn btn-success">
                                    Send To Practices
                                </button>
                            {!! Form::close() !!}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

@stop
