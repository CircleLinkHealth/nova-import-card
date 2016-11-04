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
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-6">
                        <h1>Care Plan Editor</h1>
                    </div>
                    <div class="col-sm-6">
                        <div class="pull-right" style="margin:20px;">
                            <!-- Trigger the modal with a button -->
                            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#carePlanDuplicateDetail">Duplicate Care Plan</button>
                            <!-- Modal -->
                            <div id="carePlanDuplicateDetail" class="modal fade" role="dialog">
                                <div class="modal-dialog">

                                    <!-- Modal content-->
                                    <div class="modal-content">
                                        {!! Form::open(array('url' => URL::route('admin.careplans.duplicate', array('id' => $carePlan->id)), 'class' => 'form-horizontal')) !!}
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            <h4 class="modal-title">Duplicate</h4>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <div class="col-sm-2">{!! Form::label('user_id_copy', 'User:') !!}</div>
                                                <div class="col-sm-4">{!! Form::select('user_id_copy', array('' => 'No User') + $users, $carePlan->user_id, ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-sm-2">{!! Form::label('name_copy', 'Name:') !!}</div>
                                                <div class="col-sm-10">{!! Form::text('name_copy', $carePlan->name .'-copy', ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-sm-2">{!! Form::label('display_name_copy', 'Display Name:') !!}</div>
                                                <div class="col-sm-10">{!! Form::text('display_name_copy', $carePlan->display_name .' Copy', ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                                            </div>

                                            <div class="form-group">
                                                <div class="col-sm-2">{!! Form::label('type_copy', 'Type:') !!}</div>
                                                <div class="col-sm-10">{!! Form::select('type_copy', array('test' => 'test', 'Provider Default' => 'Provider Default', 'Patient Default' => 'Patient Default'), $carePlan->type, ['class' => 'form-control select-picker', 'style' => 'width:50%;']) !!}</div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                            {!! Form::submit('Copy', array('class' => 'btn btn-success')) !!}
                                        </div>
                                        {!! Form::close() !!}
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Edit Care Plan: {{ $carePlan->id }}</div>
                    <div class="panel-body">
                        {!! Form::open(array('url' => URL::route('admin.careplans.update', array('id' => $carePlan->id)), 'class' => 'form-horizontal')) !!}
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
                            <div class="col-sm-10">{!! Form::select('type', array('Practice Default' => 'Practice Default'), $carePlan->type, ['class' => 'form-control select-picker', 'style' => 'width:50%;']) !!}</div>
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
