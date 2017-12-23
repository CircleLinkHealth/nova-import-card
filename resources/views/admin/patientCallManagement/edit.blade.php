@extends('partials.adminUI')

@section('content')
    @push('scripts')
        <script type="text/javascript" src="{{ asset('/js/admin/patientCallManagement.js') }}"></script>
    @endpush
    
    {!! Form::open(array('url' => URL::route('admin.patientCallManagement.update', array('id' => $call->id)), 'class' => 'form-horizontal')) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>Edit Call</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Edit Call: {{ $call->name }}</div>
                    <div class="panel-body">
                        @include('errors.errors')
                        @include('errors.messages')
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <td><strong>id</strong></td>
                                    <td><strong>patient</strong></td>
                                    <td><strong>nurse</strong></td>
                                    <td><strong>status</strong></td>
                                    <td><strong>window_start</strong></td>
                                    <td><strong>window_end</strong></td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><a href="{{ URL::route('admin.patientCallManagement.edit', array('id' => $call->id)) }}" class="btn btn-primary">{{ $call->id }} Detail</a></td>
                                    <td>{{ $call->inbound_cpm_id }}</td>
                                    <td>{{ $call->outbound_cpm_id }}</td>
                                    <td>{{ $call->status }}</td>
                                    <td>{{ $call->window_start }}</td>
                                    <td>{{ $call->window_end }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="form-group" style="margin-top:50px;">
                            <div class="col-xs-2">{!! Form::label('outbound_cpm_id', 'Assigned Nurse:') !!}</div>
                            <div class="col-xs-4">{!! Form::select('outbound_cpm_id', array('unassigned' => 'Unassigned') + $nurses->all(), $call->outbound_cpm_id, ['class' => 'form-control select-picker', 'style' => 'width:50%;']) !!}</div>
                            <div class="col-xs-6"></div>
                        </div>

                        <div class="form-group">
                            <div id="tBox"></div>

                            <div class="col-sm-2">{!! Form::label('window_start', 'Window Start Time:') !!}</div>
                            <div class="col-sm-4"><input id="window_start" class="form-control" name="window_start" type="input" value="{{ (old('window_start') ? old('window_start') : ($call->window_start ? $call->window_start : '')) }}"  data-field="time" data-format="HH:mm" /><span class="help-block">{{ $errors->first('window_start') }}</span></div>
                            <div class="col-sm-2">{!! Form::label('window_end', 'Window End Time:') !!}</div>
                            <div class="col-sm-4"><input id="window_end" class="form-control" name="window_end" type="input" value="{{ (old('window_end') ? old('window_end') : ($call->window_end ? $call->window_end : '')) }}"  data-field="time" data-format="HH:mm" /><span class="help-block">{{ $errors->first('window_end') }}</span></div>
                        </div>

                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::route('admin.patientCallManagement.index', array()) }}" class="btn btn-danger">Back</a>
                                    {!! Form::submit('Update Call', array('class' => 'btn btn-success')) !!}
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
