@extends('partials.adminUI')

@section('content')
    <script type="text/javascript" src="{{ asset('/js/admin/patientCallManagement.js') }}"></script>
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

                        <div class="row">
                            {!! Form::open(array('url' => URL::route('admin.patientCallManagement.index', array()), 'method' => 'get', 'class' => 'form-horizontal')) !!}
                        </div>



                        <a class="btn btn-info panel-title" data-toggle="collapse" data-parent="#accordion" href="#collapseFilter">Toggle Filters</a><br /><br />
                        <div id="collapseFilter" class="panel-collapse collapse">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-xs-2"><label for="date">Date:</label></div><div id="dtBox"></div>
                                    <div class="col-xs-4"><input id="date" class="form-control" name="date" type="input" value="{{ (old('date') ? old('date') : ($date ? $date : '')) }}"  data-field="date" data-format="yyyy-MM-dd" /><span class="help-block">{{ $errors->first('date') }}</span></div>
                                </div>

                                <div class="row">
                                    <div class="col-xs-2"><label for="filterNurse">Nurse:</label></div>
                                    <div class="col-xs-4">{!! Form::select('filterNurse', array('all' => 'All', 'unassigned' => 'Unassigned') + $nurses, $filterNurse, ['class' => 'form-control select-picker', 'style' => 'width:50%;']) !!}</div>
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


                        {!! Form::open(array('url' => URL::route('admin.patientCallManagement.index', array()), 'method' => 'get', 'class' => 'form-horizontal')) !!}
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th></th>
                                <th>Nurse</th>
                                <th>Patient</th>
                                <th>Date</th>
                                <th>Contact Window</th>
                                <th>Status</th>
                                <th>Last Date called</th>
                                <th>CCM Time to date</th>
                                <th># success</th>
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
                                        <td>
                                            @if($call->outboundUser)
                                                <a href="{{ URL::route('admin.users.edit', array('patient' => $call->outboundUser->ID)) }}" class="">{{ $call->outboundUser->display_name }}</a>
                                            @else
                                                <em style="color:red;">unassigned</em>
                                            @endif
                                        </td>
                                        <td>
                                            @if($call->inboundUser)
                                                <a href="{{ URL::route('admin.users.edit', array('patient' => $call->inboundUser->ID)) }}" class="">{{ $call->inboundUser->display_name }}</a>
                                            @else
                                                <em style="color:red;">unassigned</em>
                                            @endif
                                        </td>
                                        <td><span style="font-weight:bold;">{{ $call->call_date }}</span>
                                        </td>
                                        <td><span style="font-weight:bold;">{{ $call->window_start }}-{{ $call->window_end }}</span></td>
                                        <td>
                                            @if($call->status == 'reached')
                                                <span class="text-success"><i class="glyphicon glyphicon-ok">-Reached</i></span>
                                            @elseif($call->status == 'scheduled')
                                                <span class="text-warning"><i class="glyphicon glyphicon-list">-Scheduled</i></span>
                                            @endif
                                        </td>
                                        <td>-</td>
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
                                            @if($call->inboundUser && $call->inboundUser->patientCareTeamMembers && $call->inboundUser->patientCareTeamMembers->where('type', 'billing_provider')->first())
                                                {{ $call->inboundUser->patientCareTeamMembers->where('type', 'billing_provider')->first()->member->display_name }}
                                            @else
                                                <em style="color:red;">-</em>
                                            @endif
                                        </td>
                                        <td>
                                            @if($call->inboundUser && $call->inboundUser->primaryProgram)
                                                {{ $call->inboundUser->primaryProgram->display_name }}
                                            @else
                                                <em style="color:red;">unassigned</em>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if(Entrust::can('users-edit-all'))
                                                <a href="{{ URL::route('admin.patientCallManagement.edit', array('id' => $call->id)) }}" class="btn btn-primary btn-xs"><i class="glyphicon glyphicon-edit"></i> Edit</a>
                                            @endif
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
                            <div class="col-xs-6">Nurse:&nbsp;&nbsp;{!! Form::select('assigned_nurse', array('unassigned' => 'Unassigned') + $nurses, 'unassigned', ['class' => '', 'style' => 'width:50%;']) !!}</div>
                            <div class="col-xs-2">
                                <button type="submit" value="Submit" class="btn btn-primary btn-xs" style="margin-left:10px;"><i class="glyphicon glyphicon-circle-arrow-right"></i> Perform Action</button>
                            </div>
                        </div>
                        </form>
                        {{ $calls->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div>

@stop