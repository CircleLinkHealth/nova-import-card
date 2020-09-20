@extends('partials.providerUI')

@section('title', 'Create Practice')
@section('activity', 'Create Practice')

@section('content')
    @push('styles')
        <style>
            .form-group {
                margin: 20px;
            }
        </style>
    @endpush
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">

                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Add New Practice
                    </div>
                    <div class="panel-body">
                        @include('core::partials.errors.errors')

                        {!! Form::open(array('url' => route('saas-admin.practices.store', array()), 'class' => 'form-horizontal')) !!}

                        <div class="form-group">

                            <div class="row" style="margin-top:20px;">
                                <div class="col-xs-2">{!! Form::label('display_name', 'Display Name:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('display_name', '', ['class' => 'form-control', 'style' => 'width:100%;', 'required' => 'required']) !!}</div>
                            </div>

                            <div class="row" style="margin-top:20px;">
                                <div class="col-xs-2">
                                    {!! Form::label('service', 'Service') !!}
                                </div>
                                <div class="col-xs-7">
                                    <select class="form-control" name="service_id">
                                        @foreach(CircleLinkHealth\Customer\Entities\ChargeableService::all() as $service)
                                            <option value="{{$service->id}}" @if($service->code == 'CPT 99490'){{'selected'}}@endif>{{$service->code}}
                                                - {{$service->description}}</option>
                                        @endforeach
                                    </select>
                                </div>


                                <div class="col-xs-1">
                                    {!! Form::label('amount', 'Price ($)') !!}
                                </div>
                                <div class="col-xs-2">
                                    <input class="form-control" name="amount" type="number" step="0.01" required
                                           style="width: 100%"/>
                                </div>
                            </div>

                            <div class="row" style="margin-top:20px;">
                                <div class="col-xs-2">{!! Form::label('term_days', 'Invoice Terms (days)') !!}</div>
                                <div class="col-xs-2">
                                    <input class="form-control" name="term_days" style="width: 100%" type="number"
                                           value="30"/>
                                </div>
                                <div class="col-xs-1">{!! Form::label('active', 'Active') !!}</div>
                                <div class="col-xs-1">
                                    <input id="active" name="active" checked type="checkbox" class="form-control">
                                    <label for="active"><span> </span></label>
                                </div>
                            </div>
                        </div>


                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ route('saas-admin.practices.index', array()) }}"
                                       class="btn btn-danger">Cancel</a>
                                    {!! Form::submit('Add Practice', array('class' => 'btn btn-success')) !!}
                                </div>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop