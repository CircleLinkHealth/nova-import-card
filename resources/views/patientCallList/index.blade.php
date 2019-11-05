@extends('partials.providerUI')

@section('title', 'Patient Call List')
@section('activity', 'Patient Call List')

<?php
function formatTime($time)
{
    $seconds = $time;
    $H       = floor($seconds / 3600);
    $i       = ($seconds / 60) % 60;
    $s       = $seconds % 60;

    return sprintf('%02d:%02d:%02d', $H, $i, $s);
}
?>

@section('content')
    @push('styles')
        <link href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css"
              integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU"
              crossorigin="anonymous">

        <style>
            .red {
                color: #ba1d18;
            }

            .bold-row {
                font-weight: 900;
                color: #888888;
                text-shadow: 1px 0 #888888;
                letter-spacing: 1px;
            }

            div.dataTables_wrapper div.dataTables_filter label {
                margin-top: 2%;
            }

            div.dataTables_wrapper div.dataTables_filter input {
                height: 25px;
                width: 300px;
                margin-top: -2%;
            }

            .dataTables_wrapper .dataTables_paginate {
                visibility: hidden;
            }

            .dataTables_wrapper .dataTables_length label {
                padding-top: 10%;
            }

        </style>
    @endpush
    @push('scripts')
        <script type="text/javascript" src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
        <script>
            $(document).ready(function () {
                const table = $('#cpmEditableTable');
                table.DataTable({
                    order: [[3, "desc"]],
                    processing: true,
                    scrollX: true,
                    fixedHeader: true,
                    dom: '<"top"fi>rt<"bottom"flp><"clear">',
                    pageLength: 50,


                });

                // $('#filter-select').change(function () {
                //     table.column($(this).data('column'))
                //         .search($(this).val())
                //         .draw();
                // });

                function addClickListener() {
                    const row = $('.patientNameLink');
                    row.click(function () {
                        const callId = $(this).attr('call-id');

                        const noteModal = $("#attemptNoteCall" + callId);
                        if (callId && noteModal.length) {
                            noteModal.modal();
                            return false;
                        }
                        return true;
                    });

                    row.tooltip({boundary: 'window'});
                }

                addClickListener();

                //make sure we add the click listener when we change the page
                table.on('page.dt', function () {
                    setTimeout(addClickListener, 500);
                })

            });
        </script>
    @endpush


    <div class="row" style="margin-top:60px;">
        <div class="main-form-container col-lg-10 col-lg-offset-1 col-md-10 col-md-offset-1">
            <div class="row">
                <div class="main-form-title col-lg-12">
                    Patient Activities
                </div>
                <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">
                    <div class="">
                        <br/>
                        <br/>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-sm-3">

                                    </div>
                                    <div class="col-sm-12">
                                        {!! Form::open(array('url' => route('patientCallList.index', array()), 'method' => 'get', 'class' => 'form-horizontal')) !!}
                                        <div id="filters" class="" style="margin:0;">
                                            <div class="form-group">
                                                <div id="dtBox"></div>
                                                <label for="filterPriority"
                                                       class="col-sm-1 control-label" style="margin-left: -2%;">Activities: </label>
                                                <div class="col-sm-4">
                                                    {!! Form::select('filterPriority', array('all' => 'See All', 'priority' => 'Priority'), $filterPriority, ['class' => 'form-control select-picker', 'style'=>'margin-left:-4%; width: 30%']) !!}
                                                </div>

                                                <label for="filterStatus"
                                                       class="col-sm-1 control-label"
                                                       style="margin-left: -24%;">Status: </label>
                                                <div class="col-sm-4">
                                                    {{--@todo: This is what i need to do here: ['reached', 'done'] => 'Completed'. I need reached and done under 'Completed'--}}
                                                    {!! Form::select('filterStatus', array('all' => 'See All', 'scheduled' => 'Scheduled', 'reached' => 'Completed', 'done' => 'Done Tasks'), $filterStatus, ['class' => 'form-control select-picker', 'style' => 'width:32%; margin-left:-55%;']) !!}
                                                </div>
                                                <div class="col-sm-2">

                                                </div>
                                                <div class="col-sm-2" style="margin-left: -46%">
                                                    <button type="submit" class="btn btn-primary"
                                                            style="background: #50b2e2">
                                                        <i class="glyphicon glyphicon-sort"></i> Apply Filter
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="">
                                    @include('errors.errors')
                                    @include('errors.messages')
                                    @push('styles')
                                        <style>
                                            .table tbody > tr > td.vert-align {
                                                vertical-align: middle;
                                            }

                                            #cpmEditableTable tbody > tr > td {
                                                white-space: nowrap;
                                            }

                                            div.dataTables_filter {
                                                margin-top: -4%;
                                            }

                                            div.dataTables_filter label {
                                                display: flex;
                                            }

                                            th, td {
                                                white-space: nowrap;
                                            }

                                        </style>
                                    @endpush
                                    <table style="" id="cpmEditableTable" class="display" width="100%"
                                           cellspacing="0">
                                        <thead>
                                        <tr>
                                            <th>Task</th>
                                            <th>Patient</th>
                                            <th>Activity<br>Date</th>
                                            <th>Activity<br>Time Start</th>
                                            <th>Activity<br>Time End</th>
                                            <th>Time<br>Zone</th>
                                            <th>Last<br>Date called</th>
                                            <th>CCM<br>Time to date</th>
                                            <th># Calls<br>to date</th>
                                            <th>Provider</th>
                                            <th>Practice</th>
                                        </tr>
                                        </thead>
                                        <tbody>

                                        @if (count($calls) > 0)
                                            @foreach($calls as $key => $call)
                                                <?php
                                                $curTime   = \Carbon\Carbon::now();
                                                $curDate   = $curTime->toDateString();
                                                $curTime   = $curTime->toTimeString();
                                                $rowBg     = '';
                                                $boldRow   = '';
                                                $textBlack = '';
                                                if ($call->scheduled_date == $curDate && $call->call_time_end < $curTime) {
                                                    $rowBg = 'background-color: rgba(255, 0, 0, 0.4);';
                                                }
                                                if ('Call Back' === $call->type || $call->asap && 'reached' !== $call->status && 'done' !== $call->status) {
                                                    $boldRow   = 'bold-row';
                                                    $textBlack = 'color:black;';
                                                }
                                                ?>
                                                <tr class="{{$boldRow}}" style="{{ $rowBg . $textBlack }}">
                                                    <td class="vert-align" style="text-align:center">
                                                        @if(empty($call->type) || $call->type === 'call')
                                                            <i class="fas fa-phone"></i>
                                                        @elseif ($call->type === 'Call Back')
                                                            <img style="text-align: center"
                                                                 src="img/scheduled_activities_callback.svg"
                                                                 alt="callback image">
                                                        @elseif ($call->type === 'addendum_response')
                                                            <img style="text-align: center"
                                                                 src="img/scheduled_activities_message.svg"
                                                                 alt="callback image">
                                                        @else
                                                            <span>{{$call->type}}</span>
                                                        @endif
                                                        @if(!empty($call->attempt_note))
                                                            <button type="button"
                                                                    class="btn btn-xs btn-info glyphicon glyphicon-envelope"
                                                                    data-toggle="modal"
                                                                    data-target="#attemptNoteCall{{ $call->id }}"></button>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('patient.careplan.print', array('patient' => $call->patient_id)) }}"
                                                           class="patientNameLink" call-id="{{ $call->id }}"
                                                           style="font-weight:bold;"
                                                           data-template='<div class="tooltip" style="text-align:left" role="tooltip"><div class="arrow"></div><div class="tooltip-inner" style="text-align:left"></div></div>'
                                                           data-toggle="tooltip"
                                                           data-container="body"
                                                           data-placement="right"
                                                           data-html="true"
                                                           title="{{$call->preferredCallDaysToExpandedString()}}">
                                                            {{ $call->patient }}
                                                        </a>
                                                    </td>
                                                    <td class="{{ \Carbon\Carbon::parse($call->scheduled_date)->lessThan(\Carbon\Carbon::today()) ? 'red' : '' }}">
                                                        {{ presentDate($call->scheduled_date, false) }}
                                                    </td>

                                                    @if($call->asap === 1 && $call->status !== 'reached' && $call->status !== 'done')
                                                        <td>{{ 'ASAP' }}</td>
                                                        <td>{{ 'N/A' }}</td>
                                                    @else
                                                        <td>{{ $call->call_time_start }}</td>
                                                        <td>{{ $call->call_time_end }}</td>
                                                    @endif

                                                    <td>
                                                        @if($call->timezone)
                                                            <?php
                                                            $dateTime = new DateTime();
                                                            $dateTime->setTimeZone(new DateTimeZone($call->timezone));
                                                            echo '<span style="font-weight:bold;color:green;">'.$dateTime->format('T').'</a>';
                                                            ?>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{ presentDate($call->last_call) }}
                                                    </td>
                                                    <td>
                                                        @if( isset($call->ccm_time))
                                                            {{ formatTime($call->ccm_time) }}
                                                        @else
                                                            <em style="color:red;">-</em>
                                                        @endif
                                                    </td>


                                                    <td>
                                                        {{$call->no_of_calls ?? 0}}
                                                        (
                                                        <span style="color:green;">
                                                            {{$call->no_of_successful_calls ?? 0}}
                                                            </span>
                                                        )
                                                    </td>

                                                    <td>
                                                        {{$call->billing_provider}}
                                                    </td>
                                                    <td>
                                                        @if(!empty($call->practice))
                                                            {{ $call->practice }}
                                                        @else
                                                            <em style="color:red;">n/a</em>
                                                        @endif
                                                    </td>
                                                    {{--<td class="text-right vert-align">--}}
                                                    {{--@if($call->status == 'reached')--}}

                                                    {{--@elseif($call->status == 'scheduled')--}}
                                                    {{--<a href="{{ route('patientCallList.index', array('id' => $call->id, 'action' => 'unassign')) }}"--}}
                                                    {{--class="btn btn-danger btn-xs"><i--}}
                                                    {{--class="glyphicon glyphicon-remove"></i>--}}
                                                    {{--Unassign</a>--}}
                                                    {{--@endif--}}
                                                    {{--</td>--}}
                                                </tr>
                                            @endforeach
                                        @else
                                            {{-- DataTables automatically provides a `No data available in table` message --}}
                                            {{--  <tr>
                                                <td colspan="11">No calls found</td>
                                            </tr>  --}}
                                        @endif
                                        </tbody>
                                    </table>
                                    </form>
                                    <?php //$calls->links()?>
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
            @if ((!empty($call->attempt_note) || !empty($call->general_comment)) )
                <!-- Modal -->
                <div id="attemptNoteCall{{ $call->id }}" class="modal fade" role="dialog">
                    <div class="modal-dialog">

                        <!-- Modal content-->
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">Couple things about {{ $call->patient }}</h4>
                            </div>
                            <div class="modal-body">
                                @if(!empty($call->general_comment))
                                    <p style="font-size:125%"><strong>General:</strong> {{$call->general_comment }}</p>
                                @endif
                                @if(!empty($call->attempt_note))
                                    <p style="font-size:125%"><strong>This Call:</strong> {{ $call->attempt_note }}</p>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                <a href="{{ route('patient.careplan.print', array('patient' => $call->patient_id)) }}"
                                   class="btn btn-primary">Continue to care plan</a>
                            </div>
                        </div>

                    </div>
                </div>
            @endif
        @endforeach
    @endif
@stop