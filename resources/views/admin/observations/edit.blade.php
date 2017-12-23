@extends('partials.adminUI')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    @endpush
    {!! Form::open(array('url' => URL::route('admin.observations.update', array('id' => $observation->qid)), 'class' => 'form-horizontal')) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>Edit Observation</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Edit Observation: {{ $observation->id }}</div>
                    <div class="panel-body">
                        @include('errors.errors')
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <td></td>
                                    <td><strong>obs_msg_id</strong></td>
                                    <td><strong>user_id</strong></td>
                                    <td><strong>sequence_id</strong></td>
                                    <td><strong>obs_date</strong></td>
                                    <td><strong>obs_key</strong></td>
                                    <td><strong>obs_method</strong></td>
                                    <td><strong>obs_value</strong></td>
                                    <td><strong>obs_unit</strong></td>
                                    <td><strong>program_id</strong></td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><a href="{{ URL::route('admin.observations.show', array('id' => $observation->id)) }}" class="btn btn-primary">{{ $observation->qid }} Detail</a></td>
                                    <td><div class="btn btn-orange btn-xs">{{ $observation->obs_message_id }}</div></td>
                                    <td>{{ $observation->user_id }}</td>
                                    <td>{{ $observation->sequence_id }}</td>
                                    <td>{{ $observation->obs_date }}</td>
                                    <td>{{ $observation->obs_key }}</td>
                                    <td>{{ $observation->obs_method }}</td>
                                    <td>{{ $observation->obs_value }}</td>
                                    <td>{{ $observation->obs_unit }}</td>
                                    <td>{{ $observation->program_id }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('obs_message_id', 'Msg ID:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('obs_message_id', $observation->obs_message_id, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('comment_id', 'Comment Id:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('comment_id', $observation->comment_id, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('obs_key', 'Obs Key:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('obs_key', $observation->obs_key, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>


                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::route('admin.observations.index', array()) }}" class="btn btn-danger">Cancel</a>
                                    {!! Form::submit('Update Observation', array('class' => 'btn btn-success', 'disabled' => 'disabled')) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@stop
