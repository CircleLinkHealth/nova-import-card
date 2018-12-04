@extends('partials.adminUI')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
        <style>
            div.pad-top-0 {
                padding-top: 0px;
            }

            div.pad-30 {
                padding: 30px;
            }

            button.left-10 {
                margin-left: 10px;
            }
        </style>
    @endpush

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading"><h3>Operations Dashboard CSV</h3></div>
                    <div class="panel-body">
                        <h5>Call Center Operations Report from: <strong>{{$date->copy()->subDay()->setTimeFromTimeString('23:30')->toDateTimeString()}}</strong>, to: <strong>{{$date->toDateTimeString()}}</strong>.</h5>
                            <li>
                                <a href="{{route('OpsDashboard.makeCsv', [
                                'fileName' => $file['name'],
                                'collection' => $file['collection']])}}">Download Report</a>
                            </li>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop