@extends('cpm-admin::partials.adminUI')

@section('content')
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">

                <div class="panel panel-default">
                    <div class="panel-heading">Sales Report Generator</div>
                    <div class="panel-body">
                        <h3>Generated Reports:</h3>
                        @foreach($reports as $key => $value)
                            <li><a href="{{url('/admin/download/'. $value)}}">{{$key}}</a></li>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>


    @push('scripts')
        <script>
            $('.collapse').collapse();
        </script>
    @endpush
@stop
