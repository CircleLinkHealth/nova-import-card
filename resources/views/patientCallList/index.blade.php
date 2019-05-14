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
                color:#888888;
                text-shadow: 1px 0 #888888;
                letter-spacing:1px;
            }

        </style>
    @endpush
    @push('scripts')
        <script type="text/javascript" src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
        <script>
            $(document).ready(function () {

                const table = $('#cpmEditableTable');
                table.DataTable({
                    "order": [],
                    "iDisplayLength": 100,
                    scrollX: true,
                    fixedHeader: true
                });

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
                    Scheduled Activities
                </div>
                <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">

                    <div class="">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-sm-3">
                                        {{--<h1>Patient Call List</h1>--}}
                                        {{--<p>My assigned calls</p>--}}
                                    </div>
                                    <div class="col-sm-12">
                                        {!! Form::open(array('url' => route('patientCallList.index', array()), 'method' => 'get', 'class' => 'form-horizontal')) !!}
                                        <div id="filters" class="" style="margin:0;">
                                            <div class="form-group">
                                                <div id="dtBox"></div>
                                                <label for="date" class="col-sm-1 control-label">Date: </label>
                                                <div class="col-sm-4">
                                                    <input id="date" class="form-control pull-right" name="date"
                                                           type="text"
                                                           placeholder="yyyy-mm-dd"
                                                           value="{{ (old('date') ? old('date') : ($dateFilter ? $dateFilter : '')) }}"
                                                           data-field="date" data-format="yyyy-mm-dd"/><span
                                                            class="help-block">{{ $errors->first('date') }}</span>
                                                </div>
                                                <label for="filterStatus"
                                                       class="col-sm-1 control-label">Status: </label>
                                                <div class="col-sm-4">
                                                    {!! Form::select('filterStatus', array('all' => 'All', 'scheduled' => 'Scheduled', 'reached' => 'Reached'), $filterStatus, ['class' => 'form-control select-picker', 'style' => 'width:50%;']) !!}
                                                </div>
                                                <div class="col-sm-2">

                                                </div>
                                                <div class="col-sm-2">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="glyphicon glyphicon-sort"></i> Apply Filter
                                                    </button>
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

                                        <h3>Scheduled Activities</h3>
                                        @push('styles')
                                            <style>
                                                .table tbody > tr > td.vert-align {
                                                    vertical-align: middle;
                                                }

                                                #cpmEditableTable tbody > tr > td {
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
                                                <th>Activity Date</th>
                                                <th>Activity Time Start</th>
                                                <th>Activity Time End</th>
                                                <th>Time Zone</th>
                                                <th>Last Date called</th>
                                                <th>CCM Time to date</th>
                                                <th>BHI Time to date</th>
                                                <th># Calls to date</th>
                                                <th>Provider</th>
                                                <th>Practice</th>
                                                {{--<th></th>--}}
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @if (count($calls) > 0)
                                                @foreach($calls as $key => $call)
                                                    <?php
                                                    $curTime = \Carbon\Carbon::now();
                                                    $curDate = $curTime->toDateString();
                                                    $curTime = $curTime->toTimeString();
                                                    $rowBg   = '';
                                                    $boldRow = '';
                                                    if ($call->scheduled_date == $curDate && $call->call_time_end < $curTime) {
                                                        $rowBg = 'background-color: rgba(255, 0, 0, 0.4);';
                                                    }
                                                    if ('Call Back' === $call->type) {
                                                        $boldRow = 'bold-row';
                                                    }
                                                    ?>
                                                    <tr class="{{$boldRow}}" style="{{ $rowBg }}">
                                                        <td class="vert-align" style="text-align:center">
                                                            @if(empty($call->type) || $call->type === 'call')
                                                                <i class="fas fa-phone"></i>
                                                            @elseif ($call->type === 'Call Back')
                                                                <i class="fas fa-phone"></i> Back
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
                                                               style="text-decoration:underline;font-weight:bold;"
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
                                                        <td>{{ $call->call_time_start }}</td>
                                                        <td>{{ $call->call_time_end }}</td>
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
                                                            @if( isset($call->bhi_time))
                                                                {{ formatTime($call->bhi_time) }}
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