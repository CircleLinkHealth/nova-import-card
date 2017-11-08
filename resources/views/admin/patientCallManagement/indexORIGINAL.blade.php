@extends('partials.adminUI')

@section('content')
    @push('scripts')
        <script type="text/javascript" src="{{ asset('/js/admin/patientCallManagement.js') }}"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
    @endpush
    @push('styles')
        <link href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css" rel="stylesheet">
    @endpush
    <div id="nurseFormWrapper" style="display:none;">
        {!! Form::select('nurseFormSelect', array('unassigned' => 'Unassigned') + $nurses->all(), '', ['class' => 'select-picker', 'style' => 'width:150px;']) !!}
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h1>Patient Call Management</h1>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Manage Patient Calls</div>
                    <div class="panel-body">
                        @include('errors.errors')
                        @include('errors.messages')

                        <div id="dtBox"></div>
                        <div id="tBox"></div>

                        <div class="row">
                            <div class="col-sm-12">
                                {!! Form::open(array('url' => URL::route('admin.patientCallManagement.index', array()), 'method' => 'get', 'class' => 'form-horizontal')) !!}
                                <a class="btn btn-info panel-title" data-toggle="collapse" data-parent="#accordion" href="#collapseFilter">Toggle Filters</a><br /><br />
                                <div id="collapseFilter" class="panel-collapse collapse">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2"><label for="date">Date:</label></div><div id="dtBox"></div>
                                            <div class="col-xs-4"><input id="date" class="form-control" name="date" type="input" value="{{ (old('date') ? old('date') : ($date ? $date : '')) }}"  data-field="date" data-format="yyyy-MM-dd" /><span class="help-block">{{ $errors->first('date') }}</span></div>
                                        </div>

                                        <div class="row">
                                            <div class="col-xs-2"><label for="date">Date:</label></div>
                                            <div class="col-xs-4"><input id="date" class="form-control" name="date" type="input" value="{{ (old('date') ? old('date') : ($date ? $date : '')) }}"  data-field="date" data-format="yyyy-MM-dd" /><span class="help-block">{{ $errors->first('date') }}</span></div>
                                        </div>

                                        <div class="row">
                                            <div class="col-xs-2"><label for="filterNurse">Nurse:</label></div>
                                            <div class="col-xs-4">{!! Form::select('filterNurse', array('all' => 'All', 'unassigned' => 'Unassigned') + $nurses->all(), $filterNurse, ['class' => 'form-control select-picker', 'style' => 'width:50%;']) !!}</div>
                                        </div>

                                        <div class="row" style="margin-top:15px;">
                                            <div class="col-xs-2"><label for="filterStatus">Status:</label></div>
                                            <div class="col-xs-4">{!! Form::select('filterStatus', array('all' => 'All', 'scheduled' => 'Scheduled', 'reached' => 'Reached'), $filterStatus, ['class' => 'form-control select-picker', 'style' => 'width:50%;']) !!}</div>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-top:50px;">
                                        <div class="col-sm-12">
                                            <div class="" style="text-align:center;">
                                                {!! Form::hidden('action', 'filter') !!}
                                                <button type="submit" class="btn btn-primary"><i class="glyphicon glyphicon-sort"></i> Apply Filters</button>
                                                <a href="{{ URL::route('admin.patientCallManagement.index', array()) }}" class="btn btn-primary"><i class="glyphicon glyphicon-refresh"></i> Reset Filters</a>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {!! Form::close() !!}
                            </div>
                        </div>


                        {!! Form::open(array('url' => URL::route('admin.patientCallManagement.index', array()), 'method' => 'get', 'class' => 'form-horizontal')) !!}
                        <style>
                            .table tbody>tr>td.vert-align{
                                vertical-align: middle;
                            }

                            #cpmEditableTable tbody>tr>td {
                                white-space: nowrap;
                            }

                            .cpm-editable {
                                color:#000;
                            }

                            .highlight {
                                color:green;
                                font-weight:bold;
                            }
                        </style>
                        <table style=""  id="cpmEditableTable" class="display" width="100%" cellspacing="0">
                            <thead>
                            <tr>
                                <th></th>
                                <th>Status</th>
                                <th>Nurse</th>
                                <th>Patient</th>
                                <th>Next Call Date</th>
                                <th>Next Call Time Start</th>
                                <th>Next Call Time End</th>
                                <th>Last Date called</th>
                                <th>CCM Time to date</th>
                                <th># Calls to date</th>
                                <th>Provider</th>
                                <th>Program</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @if (count($calls) > 0)
                                @foreach($calls as $call)
                                    <tr>
                                        <td><input type="checkbox" name="calls[]" value="{{ $call->id }}"></td>
                                        <td class="vert-align">
                                            @if($call->status == 'reached')
                                                <span class="btn btn-success btn-xs"><i class="glyphicon glyphicon-ok"></i> Reached</span>
                                            @elseif($call->status == 'scheduled')
                                                <span class="btn btn-warning btn-xs"><i class="glyphicon glyphicon-list"></i> Scheduled</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($call->outboundUser)
                                                {{ $call->outboundUser->display_name }}
                                            @else
                                                <em style="color:red;">unassigned</em>
                                            @endif
                                        </td>
                                        <td>
                                            @if($call->inboundUser)
                                                <a href="{{ URL::route('patient.demographics.show', array('patient' => $call->inboundUser->id)) }}">{{ $call->inboundUser->display_name }} </a>
                                            @else
                                                <em style="color:red;">unassigned</em>
                                            @endif
                                        </td>
                                        <td class="cpm-editable" field="scheduled_date" data-value="{{ $call->call_date }}">{{ $call->call_date }} <a href="#"><span class="glyphicon glyphicon-edit cpm-editable-icon" call-id="{{ $call->id }}" column-name="scheduled_date" column-value="{{ $call->call_date }}"></span></a>
                                        </td>
                                        <td class="cpm-editable" field="window_start" data-value="{{ $call->window_start }}">{{ $call->window_start }} <a href="#"><span class="glyphicon glyphicon-edit cpm-editable-icon" call-id="{{ $call->id }}" column-name="window_start" column-value="{{ $call->window_start }}"></span></a>
                                        </td>
                                        <td class="cpm-editable" field="window_end" data-value="{{ $call->window_end }}">{{ $call->window_end }} <a href="#"><span class="glyphicon glyphicon-edit cpm-editable-icon" call-id="{{ $call->id }}" column-name="window_end" column-value="{{ $call->window_end }}"></span></a>
                                        </td>
                                        <td>
                                            @if($call->inboundUser)
                                                {{ $call->inboundUser->patientInfo->last_successful_contact_time }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($call->inboundUser)
                                                {{ $call->inboundUser->patientInfo->currentMonthCCMTime }}
                                            @else
                                                <em style="color:red;">-</em>
                                            @endif
                                        </td>
                                        <td>
                                            @if($call->inboundUser)                                                                             {{ \App\Call::numberOfCallsForPatientForMonth($call->inboundUser,Carbon\Carbon::now()->toDateTimeString()) }} (<span style="color:green;">{{ \App\Call::numberOfSuccessfulCallsForPatientForMonth($call->inboundUser,Carbon\Carbon::now()->toDateTimeString()) }}</span>)
                                            @endif
                                        </td>
                                        <td>
                                            @if($call->inboundUser && $call->inboundUser->careTeamMembers && $call->inboundUser->careTeamMembers->where('type', 'billing_provider')->first())
                                                {{ $call->inboundUser->careTeamMembers->where('type', 'billing_provider')->first()->member->display_name }}
                                            @else
                                                <em style="color:red;">-</em>
                                            @endif
                                        </td>
                                        <td>
                                            @if($call->inboundUser && $call->inboundUser->primaryPractice)
                                                {{ $call->inboundUser->primaryPractice->display_name }}
                                            @else
                                                <em style="color:red;">n/a</em>
                                            @endif
                                        </td>
                                        <td class="text-right vert-align">
                                            <a href="{{ URL::route('admin.patientCallManagement.edit', array('id' => $call->id)) }}" class="btn btn-primary btn-xs"><i class="glyphicon glyphicon-edit"></i> Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="7">No calls found</td></tr>
                            @endif
                            </tbody>
                        </table>
                        <div class="row" style="margin:40px 0px;">
                            <div class="col-xs-4">
                                With selected calls:&nbsp;&nbsp;
                                <select name="action">
                                    <option value="assign">Assign Nurse:</option>
                                </select>
                            </div>
                            <div class="col-xs-6">Nurse:&nbsp;&nbsp;
                                {!! Form::select('assigned_nurse', array('unassigned' => 'Unassigned') + $nurses->all(), 'unassigned', ['class' => 'select-picker', 'style' => 'width:50%;']) !!}
                            </div>
                            <div class="col-xs-2">
                                <button type="submit" value="Submit" class="btn btn-primary btn-xs" style="margin-left:10px;"><i class="glyphicon glyphicon-circle-arrow-right"></i> Perform Action</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div>

@stop