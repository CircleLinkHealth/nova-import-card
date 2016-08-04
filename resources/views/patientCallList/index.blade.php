@extends('partials.providerUI')

@section('title', 'Patient Listing')
@section('activity', '')

@section('content')

    <script type="text/javascript" src="{{ asset('/js/admin/reports/patientCallManagement.js') }}"></script>
    <div class="">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-3">
                        <h1>Patient Call List</h1>
                        <p>My assigned calls</p>
                    </div>
                    <div class="col-sm-9">
                        {!! Form::open(array('url' => URL::route('patientCallList.index', array()), 'method' => 'get', 'class' => 'form-horizontal')) !!}
                        <div id="filters" class="" style="margin:40px 0px;">
                            <div class="form-group">
                                <div id="dtBox"></div>
                                <label for="date" class="col-sm-1 control-label">Date: </label>
                                <div class="col-sm-4">
                                    <input id="date" class="form-control pull-right" name="date" type="input" value="{{ (old('date') ? old('date') : ($date ? $date : '')) }}"  data-field="date" data-format="yyyy-MM-dd" /><span class="help-block">{{ $errors->first('date') }}</span>
                                </div>
                                <label for="filterStatus" class="col-sm-1 control-label">Status: </label>
                                <div class="col-sm-4">
                                    {!! Form::select('filterStatus', array('all' => 'All', 'scheduled' => 'Scheduled', 'reached' => 'Reached'), $filterStatus, ['class' => 'form-control select-picker', 'style' => 'width:50%;']) !!}
                                </div>
                                <div class="col-sm-2">
                                    <button type="submit" class="btn btn-primary"><i class="glyphicon glyphicon-sort"></i> Apply Filter</button>
                                </div>
                            </div>
                        </div>
                        </form>
                    </div>
                </div>
                <div class="">
                    <div class="">
                        @include('errors.errors')
                        @include('errors.messages')

                        <h3>Scheduled Calls</h3>
                        <style>
                            .table tbody>tr>td.vert-align{
                                vertical-align: middle;
                            }
                        </style>
                        <table class="table table-striped" style="">
                            <thead>
                            <tr>
                                <th></th>
                                <th>Status</th>
                                <th>Patient</th>
                                <th>DOB</th>
                                <th>Date</th>
                                <th>Contact Window Start</th>
                                <th>Contact Window End</th>
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
                                        <td>
                                            <input type="checkbox" name="calls[]" value="{{ $call->id }}">
                                            @if($call->inboundUser && $call->status == 'scheduled')
                                                <a href="{{ URL::route('patient.demographics.show', array('patient' => $call->inboundUser->ID)) }}" class="btn btn-primary"><span class="glyphicon glyphicon-earphone" style="margin-right:3px;font-size: 20px;padding:5px;"></span></a>
                                            @endif
                                        </td>
                                        <td class="vert-align">
                                            @if($call->status == 'reached')
                                                <button class="btn btn-success btn-xs"><i class="glyphicon glyphicon-ok"></i> Reached</button>
                                            @elseif($call->status == 'scheduled')
                                                <button class="btn btn-warning btn-xs"><i class="glyphicon glyphicon-list"></i> Scheduled</button>
                                            @endif
                                        </td>
                                        <td>
                                            @if($call->inboundUser)
                                                <a href="{{ URL::route('patient.demographics.show', array('patient' => $call->inboundUser->ID)) }}" style="text-decoration:underline;font-weight:bold;">{{ $call->inboundUser->display_name }} </a>
                                            @else
                                                <em style="color:red;">unassigned</em>
                                            @endif
                                        </td>
                                        <td>-</td>
                                        <td>{{ $call->call_date }}</td>
                                        <td>{{ $call->window_start }}</td>
                                        <td>{{ $call->window_end }}</td>
                                        <td>-</td>
                                        <td>
                                            @if($call->inboundUser)
                                                {{ $call->inboundUser->patientInfo->currentMonthCCMTime }}
                                            @else
                                                <em style="color:red;">-</em>
                                            @endif
                                        </td>
                                        <td>-</td>
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
                                        <td class="text-right vert-align">
                                            @if($call->status == 'reached')

                                            @elseif($call->status == 'scheduled')
                                                <a href="{{ URL::route('patientCallList.index', array('id' => $call->id, 'action' => 'unassign')) }}" class="btn btn-danger btn-xs"><i class="glyphicon glyphicon-remove"></i> Unassign</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="7">No calls found</td></tr>
                            @endif
                            </tbody>
                        </table>
                        </form>
                        {{ $calls->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div>

@stop