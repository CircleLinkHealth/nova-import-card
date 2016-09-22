@extends('partials.providerUI')

@section('title', 'Patient Listing')
@section('activity', '')

@section('content')

    <script type="text/javascript" src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
    <link href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css" rel="stylesheet">
    <script>
        $(document).ready(function() {
            $('#cpmEditableTable').DataTable({
                "iDisplayLength": 100,
                scrollX: true,
                fixedHeader: true
            });

            $('.patientNameLink').click(function() {
                callId = $(this).attr('call-id');
                if ( callId && $( "#attemptNoteCall" + callId ).length ) {
                    $( "#attemptNoteCall" + callId ).modal();
                    return false;
                }
                return true;
            });
        });
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
                                                <th>Patient</th>
                                                <th>Next Call Date</th>
                                                <th>Next Call Time Start</th>
                                                <th>Next Call Time End</th>
                                                <th>Time Zone</th>
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
                                                    <?php
                                                    $curTime = \Carbon\Carbon::now();
                                                    $curDate = $curTime->toDateString();
                                                    $curTime = $curTime->toTimeString();
                                                    $rowBg = '';
                                                    if($call->scheduled_date == $curDate && $call->window_end < $curTime) {
                                                        $rowBg = 'background-color: rgba(255, 0, 0, 0.4);';
                                                    }
                                                    ?>
                                                    <tr style="{{ $rowBg }}">
                                                        <td class="vert-align">
                                                            @if(!empty($call->attempt_note))
                                                                <button type="button" class="btn btn-xs btn-info glyphicon glyphicon-envelope" data-toggle="modal" data-target="#attemptNoteCall{{ $call->id }}">Note</button>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($call->inboundUser)
                                                                <a href="{{ URL::route('patient.careplan.print', array('patient' => $call->inboundUser->ID)) }}" class="patientNameLink" call-id="{{ $call->id }}" style="text-decoration:underline;font-weight:bold;">{{ $call->inboundUser->display_name }} </a>
                                                            @else
                                                                <em style="color:red;">unassigned</em>
                                                            @endif
                                                        </td>
                                                        <td>{{ $call->scheduled_date }}</td>
                                                        <td>{{ $call->window_start }}</td>
                                                        <td>{{ $call->window_end }}</td>
                                                        <td>
                                                            @if($call->inboundUser)
                                                                <?php
                                                                $dateTime = new DateTime();
                                                                $dateTime->setTimeZone(new DateTimeZone($call->inboundUser->timezone));
                                                                echo '<span style="font-weight:bold;color:green;">' . $dateTime->format('T') . '</a>';
                                                                ?>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($call->inboundUser)
                                                                {{ $call->inboundUser->patientInfo->last_contact_time }}
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
                                                            @if($call->inboundUser && $call->inboundUser->patientCareTeamMembers && $call->inboundUser->patientCareTeamMembers->where('type', 'billing_provider')->first() && $call->inboundUser->patientCareTeamMembers->where('type', 'billing_provider')->first()->user)
                                                                {{ $call->inboundUser->patientCareTeamMembers->where('type', 'billing_provider')->first()->user->display_name }}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($call->inboundUser && $call->inboundUser->primaryProgram)
                                                                {{ $call->inboundUser->primaryProgram->display_name }}
                                                            @else
                                                                <em style="color:red;">n/a</em>
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
    </div>

    <!-- call attempt_note modals -->
    @if (count($calls) > 0)
        @foreach($calls as $call)
            @if (!empty($call->attempt_note) || ($call->inboundUser && $call->inboundUser->patientInfo && !empty($call->inboundUser->patientInfo->general_comment)) )
            <!-- Modal -->
            <div id="attemptNoteCall{{ $call->id }}" class="modal fade" role="dialog">
                <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Couple things about {{ $call->inboundUser->display_name }}</h4>
                        </div>
                        <div class="modal-body">
                            @if($call->inboundUser && $call->inboundUser->patientInfo && !empty($call->inboundUser->patientInfo->general_comment))
                                <p style="font-size:125%"><strong>General:</strong> {{ $call->inboundUser->patientInfo->general_comment }}</p>
                            @endif
                            @if(!empty($call->attempt_note))
                                <p style="font-size:125%"><strong>This Call:</strong> {{ $call->attempt_note }}</p>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <a href="{{ URL::route('patient.careplan.print', array('patient' => $call->inboundUser->ID)) }}" class="btn btn-primary">Continue to care plan</a>
                        </div>
                    </div>

                </div>
            </div>
            @endif
        @endforeach
    @endif
@stop