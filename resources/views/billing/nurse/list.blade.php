@extends('partials.adminUI')

@section('content')
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    {!! Form::open(array('url' => URL::route('admin.reports.nurse.invoice.view', array()),'class' => 'form-horizontal')) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">

                <div class="panel panel-default">
                    <div class="panel-heading">Nurse Invoice Generator</div>
                    <div class="panel-body">
                        <h3>Generated invoices</h3>
                        @foreach($invoices as $key => $value)
                            <li><button type="submit" name="name">{{$key}}</button></li>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script>

        $('.collapse').collapse();

    </script>
@stop
