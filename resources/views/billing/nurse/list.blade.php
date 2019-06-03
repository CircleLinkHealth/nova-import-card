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
                {{ csrf_field() }}
                <div class="panel panel-default">
                    <div class="panel-heading">Nurse Invoice Generator</div>
                    <div class="panel-body">
                        <h3>Generated invoices</h3>
                        @foreach($invoices as $key => $value)
                            <li>
                                <a href="{{route('download', ['filePath' => $value['link']])}}">{{$value['name']}}</a>
                            </li>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop
