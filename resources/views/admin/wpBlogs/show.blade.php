@extends('partials.adminUI')

@section('content')
    <style>
        .form-group {
            margin:20px;
        }
    </style>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>View Program</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Program ID: {{ $program->blog_id }}
                    </div>
                    <div class="panel-body">
                        @include('errors.errors')

                        <div class="row" style="">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::route('admin.programs.index', array()) }}" class="btn btn-danger">Back</a>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <h2>Program - {{ $program->domain }}</h2>
                        <p>Program Info</p>

                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-1"><strong>Domain:</strong></div>
                                <div class="col-xs-5">{!! $program->domain !!}</div>
                                <div class="col-xs-2"><strong>Location:</strong></div>
                                <div class="col-xs-4">
                                    {{ $program->location ? $program->location->name.'('.$program->location->id.')' : 'no location' }}
                                </div>
                            </div>
                        </div>

                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::route('admin.programs.index', array()) }}" class="btn btn-danger">Back</a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop