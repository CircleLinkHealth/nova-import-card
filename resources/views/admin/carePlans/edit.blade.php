@extends('partials.adminUI')

@section('content')
    <script>
        $(document).on("click", '.section-reload', function(event) {
            var sectionId = $(this).attr('section');
            var BASE = "{{ url() }}";
            var carePlanId = "{{ $carePlan->id }}";
            //alert(BASE + '/careplan/' + carePlanId + '/section/' + sectionId);
            $('#section' + sectionId)
                    .html('loading.....')
                    .load(BASE + '/careplan/' + carePlanId + '/section/' + sectionId);
            return false;
        });
    </script>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    {!! Form::open(array('url' => URL::route('admin.careplans.update', array('id' => $carePlan->id)), 'class' => 'form-horizontal')) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-6">
                        <h1>Care Plan Editor</h1>
                    </div>
                    <div class="col-sm-6">
                        <div class="pull-right" style="margin:20px;">
                            <a href="{{ URL::route('admin.careplans.create', array()) }}" class="btn btn-success">Duplicate Care Plan</a>
                            <a href="{{ URL::route('admin.careplans.create', array()) }}" class="btn btn-success">Duplicate for patient</a>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Edit Care Plan: {{ $carePlan->id }}</div>
                    <div class="panel-body">
                        @include('errors.errors')

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('user_id', 'User:') !!}</div>
                            <div class="col-sm-4">{!! Form::select('user_id', array('' => 'No User') + $users, $carePlan->user_id, ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('name', 'Name:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('name', $carePlan->name, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('display_name', 'Display Name:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('display_name', $carePlan->display_name, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('type', 'Type:') !!}</div>
                            <div class="col-sm-10">{!! Form::select('type', array('test' => 'test', 'provider' => 'provider', 'provider-default' => 'provider-default','patient' => 'patient', 'patient-default' => 'patient-default'), $carePlan->type, ['class' => 'form-control select-picker', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <h2>Sections:</h2>
                        <a href="{{ URL::route('admin.careplansections.create', array()) }}" class="btn btn-primary btn">
                            <span class="glyphicon glyphicon-plus-sign"></span>
                            Add Section
                        </a>
                        <br />












                        @if($carePlan->careSections)
                            @foreach($carePlan->careSections as $careSection)
                                @include('partials.carePlans.section')
                            @endforeach
                        @endif







                        @if($carePlan->careSections)
                            <a href="{{ URL::route('admin.careplans.index', array()) }}" class="btn btn-primary btn">
                                <span class="glyphicon glyphicon-plus-sign"></span>
                                Add Item
                            </a>
                            <h3>Section 1:</h3>
                            <a href="{{ URL::route('admin.items.show', array('id' => $carePlan->id)) }}" class="btn btn-orange btn-xs">{{ $carePlan->name }}</a>
                        @else
                            <div class="alert alert-danger" style="margin-top:20px;">
                                No sections
                            </div>
                        @endif

                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::route('admin.careplans.index', array()) }}" class="btn btn-danger">Cancel</a>
                                    {!! Form::submit('Update Care Plan', array('class' => 'btn btn-success')) !!}
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
