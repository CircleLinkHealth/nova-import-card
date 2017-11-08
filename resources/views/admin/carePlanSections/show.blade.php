@extends('partials.adminUI')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    @endpush
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>View Care Plan</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">View Care Plan: {{ $careplan->name }}</div>
                    <div class="panel-body">
                        @include('errors.errors')
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2 text-right">User:</div>
                                <div class="col-xs-4">{{ $careplan->user_id }}</div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">Name:</div>
                            <div class="col-sm-10">{{ $careplan->name }}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">Display Name:</div>
                            <div class="col-sm-10">{{ $careplan->display_name }}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">Type:</div>
                            <div class="col-sm-10">{{ $careplan->type }}</div>
                        </div>

                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::route('admin.careplansections.index', array()) }}" class="btn btn-danger">Back</a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>


    </div>
@stop
