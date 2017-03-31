@extends('partials.adminUI')

@section('content')
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                {{ csrf_field() }}
                <div class="panel panel-default">
                    <div class="panel-heading">Nurse Invoice Generator</div>
                    <div class="panel-body">
                        <h3>Generated invoices</h3>
                        @foreach($invoices as $key => $value)
                            <li>
                                <a href="{{url('/admin/download/'. $key)}}">{{$value}}</a>
                            </li>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="jumbotron" style="padding-top: 0px">
                <div class="row">
                    {{--<input type="hidden" value="{{json_encode($invoices)}}" name="links">--}}
                    {{--<input type="hidden" value="{{$month}}" name="month">--}}
                    <button id="submit" name="submit" class="btn btn-success" disabled>Send To Practices</button>
                </div>
            </div>
        </div>
    </div>

@stop
