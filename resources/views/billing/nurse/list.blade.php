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

    {!! Form::open(array('url' => route('admin.reports.nurse.send', []),'method' => 'post','id' => 'form', 'class' => 'form-horizontal')) !!}

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

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="jumbotron pad-30 pad-top-0">
                <h3>Email Preview</h3>
                <h3>Subject: {{$month}} Time and Fees Earned Report</h3>
                <span id="result">
                    <hr>

                        <p>Hi [RN Name],</p>

                        <p>Thanks for your efforts at CircleLink Health!</p>

                        <p>Attached please find a time receipt and calculation of fees payable to you for subject line hours.</p>

                        <p>Please let us know any questions or concerns. We’d like to initiate funds transfer to you in the next day or two.</p>

                        <p>Best,</p>

                        <p>CircleLink Team</p>

                </span>
                <div class="row">
                    <input type="hidden" value="{{json_encode($invoices)}}" name="links">
                    <input type="hidden" value="{{$month}}" name="month">
                    <button id="submit" name="submit" class="btn btn-success left-10">Send To RNs</button>
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}

@stop
