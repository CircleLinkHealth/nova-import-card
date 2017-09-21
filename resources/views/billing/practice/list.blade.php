@extends('partials.adminUI')

@section('content')
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                {{ csrf_field() }}
                <div class="panel panel-default">
                    <div class="panel-heading">Practice Invoices and Patient Billable Reports</div>
                    <div class="panel-body">

                        @foreach($invoices as $key => $value)
                            <li>
                                <b>{{$key}}</b>
                                <a href="{{url('/admin/reports/monthly-billing/v2/downloadInvoice/'.$value['practiceId'].'/'. $value['Invoice'])}}">Invoice</a> •
                                <a href="{{url('/admin/reports/monthly-billing/v2/downloadInvoice/'.$value['practiceId'].'/'. $value['Patient Report'])}}">Patient Report</a> •
                            </li>
                        @endforeach

                        <hr>

                        <div class="row" style="padding-left: 30px;">
                            {!! Form::open(array('url' => URL::route('monthly.billing.send', array()),'class' => 'form-horizontal')) !!}
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
