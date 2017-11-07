@extends('partials.adminUI')

@section('content')
    @push('styles')
        <style>
            .form-group {
                margin:20px;
            }
        </style>
    @endpush
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>Practice: {{ $program->display_name }}</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                            @if(Entrust::can('programs-manage'))
                                <a href="{{ URL::route('admin.programs.edit', array('id' => $program->id)) }}"
                                   class="btn btn-info">Edit</a>
                            @endif
                            <a href="{{ URL::route('admin.programs.index', array()) }}" class="btn btn-danger" style="margin-left:10px;"><i class="glyphicon glyphicon-plus-sign"></i> Back to programs list</a>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        {{ $program->display_name }}
                    </div>
                    <div class="panel-body">
                        @include('errors.errors')

                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs" role="tablist" style="margin-top:20px;">
                            <li role="presentation" class="active"><a href="#programTab" aria-controls="programTab"
                                                                      role="tab" data-toggle="tab">Practice Info</a>
                            </li>
                            <li role="presentation"><a href="#statsTab" aria-controls="careplansTab" role="tab" data-toggle="tab">Statistics</a></li>
                            <li role="presentation"><a href="#careplansTab" aria-controls="careplansTab" role="tab" data-toggle="tab">Careplans</a></li>
                        </ul>

                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="programTab">

                                <h2>Practice - {{ $program->display_name }}</h2>
                                <p>Practice Info</p>

                                <div class="form-group">

                                    <div class="row" style="margin-top:20px;">
                                        <div class="col-xs-2">Locations</div>
                                        {{--<div class="col-xs-4">{!! $program->location_id !!}</div>--}}
                                    </div>

                                    <div class="row" style="margin-top:20px;">
                                        <div class="col-xs-2">Display Name:</div>
                                        <div class="col-xs-10">{!! $program->display_name !!}</div>
                                    </div>

                                    <div class="row" style="margin-top:20px;">
                                        <div class="col-xs-2">Unique Name:</div>
                                        <div class="col-xs-10">{!! $program->name !!}</div>
                                    </div>

                                    <div class="row" style="margin-top:20px;">
                                        <div class="col-xs-2">Short Display Name:</div>
                                        <div class="col-xs-10">{!! $program->short_display_name !!}</div>
                                    </div>
                                </div>
                            </div>

                            <div role="tabpanel" class="tab-pane" id="statsTab">
                                <h3>Statistics:</h3>
                            </div>

                            <div role="tabpanel" class="tab-pane" id="careplansTab">
                                <h3>Careplans:</h3>
                                <em>No careplans found for this program.</em>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop