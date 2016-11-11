@extends('partials.adminUI')

@section('content')
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                {{ csrf_field() }}
                <div class="panel panel-default">
                    <div class="panel-heading">Nurse Invoice Generator</div>
                    {!! Form::open(array('url' => URL::route('admin.reports.nurse.send', array()),'method' => 'post','id' => 'form', 'class' => 'form-horizontal')) !!}
                    <div class="panel-body">
                            <h3>Generated invoices</h3>
                            @foreach($invoices as $key => $value)
                                <li>
                                    <a href="{{url('/admin/download/'. $value)}}">{{$key}}</a>
                                </li>
                            @endforeach
                        <div class="row">
                            <input type="hidden" value="{{json_encode($data)}}" name="data">
                            <button id="submit" name="submit" class="btn btn-success">Send To RNs</button>
                        </div>
                    </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="jumbotron text-center">
                    <h1><span id="result"></span></h1>
                </div>
            </div>
        </div>
@stop
