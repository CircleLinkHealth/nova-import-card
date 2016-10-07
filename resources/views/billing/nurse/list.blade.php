@extends('partials.adminUI')

@section('content')
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">

                <div class="panel panel-default">
                    <div class="panel-heading">Nurse Invoice Generator</div>
                    <div class="panel-body">
                        <h3>Generated invoices</h3>
                        @foreach($invoices as $nurseName => $fileName)
                            <li><a href="{{ route('download', $fileName) }}" target="_blank">{{ $nurseName }}</a></li>
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
