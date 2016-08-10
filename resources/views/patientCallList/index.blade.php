@extends('partials.providerUI')

@section('title', 'Patient Listing')
@section('activity', '')

@section('content')

    <script type="text/javascript" src="{{ asset('/js/admin/patientCallManagement.js') }}"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
    <link href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css" rel="stylesheet">
    <script>
        $(document).ready(function() {
            var cpmEditableStatus = false;
            var cpmEditableID = false;
            $('#cpmEditableTable').DataTable( {
                "scrollX": true
            } );

            $('#cpmEditableTable').on('click', '.cpm-editable-icon', function(){
                // editing!
                if(cpmEditableStatus === true) {
                    alert('already editing');
                    // force close existing edit
                    return false;
                }
                cpmEditableStatus = true;
                //alert( "Handler for .click() called." );
                dataValue = $( this ).parent().parent().attr('data-value');
                $( this ).parent().parent().html('<input type="text" id="cpm-editable-value" value="' + dataValue + '" /><a href="#" id="cpm-editable-save"><span class="glyphicon glyphicon-ok" style=""></span></a>');
                return false;
            });

            $('#cpmEditableTable').on('click', '#cpm-editable-save', function(){
                dataValue = $('#cpm-editable-value').val();
                $( this ).parent().html(dataValue + '<a href="#"><span class="glyphicon glyphicon-edit cpm-editable-icon"></span></a>');
                cpmEditableStatus = false;
                return false;
            });
        } );
    </script>

    <div class="row" style="margin-top:60px;">
        <div class="main-form-container col-lg-10 col-lg-offset-1 col-md-10 col-md-offset-1">
            <div class="row">
                <div class="main-form-title col-lg-12">
                    Patient Call List
                </div>
                <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">

                    <div class="">
                        <div class="row">
                            <div class="col-md-12">
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

                                            #cpmEditableTable tbody>tr>td {
                                                white-space: nowrap;
                                            }
                                        </style>
                                        <table style=""  id="cpmEditableTable" class="display" width="100%" cellspacing="0">
                                            <thead>
                                            <tr>
                                                <th></th>
                                                <th>Status</th>
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
                                                        <td>
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
                                                        <td class="cpm-editable" field="call_date" data-value="{{ $call->call_date }}">{{ $call->call_date }} <a href="#"><span class="glyphicon glyphicon-edit cpm-editable-icon"></span></a>
                                                        </td>
                                                        <td class="cpm-editable" field="window_start" data-value="{{ $call->window_start }}">{{ $call->window_start }} <a href="#"><span class="glyphicon glyphicon-edit cpm-editable-icon"></span></a>
                                                        </td>
                                                        <td class="cpm-editable" field="window_end" data-value="{{ $call->window_end }}">{{ $call->window_end }} <a href="#"><span class="glyphicon glyphicon-edit cpm-editable-icon"></span></a>
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
                                        <?php //$calls->links() ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>


                </div>
            </div>
        </div>
    </div>


@stop