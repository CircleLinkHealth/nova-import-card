@extends('partials.adminUI')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>View App Config</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                @include('errors.errors')
                @include('errors.messages')
                <div class="panel panel-default">
                    <div class="panel-heading">View App Config: {{ $appConfig->name }}</div>
                    <div class="panel-body">

                        <div class="row" style="margin:20px 0px;">
                            <strong>Config Key:</strong><br>
                            {{ $appConfig->config_key }}
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Config Value:</strong><br>
                            {{ $appConfig->config_value }}
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Created:</strong><br>
                            {{ date('F d, Y g:i A', strtotime($appConfig->created_at)) }}
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Updated:</strong><br>
                            {{ date('F d, Y g:i A', strtotime($appConfig->updated_at)) }}
                        </div>

                    </div>
                </div>
            </div>
        </div>


    </div>
@stop
