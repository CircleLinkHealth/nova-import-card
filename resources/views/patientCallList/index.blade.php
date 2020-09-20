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
                    order: [],
                    processing: true,
                    scrollX: true,
                    fixedHeader: true,
                    dom: '<"top"fi>rt<"bottom"lp>',
                    pageLength: 50,


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
                    Patient Activities
                </div>
                <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">
                    <div class="">

                        @if(isset($draftNotes) && $draftNotes->isNotEmpty())
                            <div class="row text-center">
                                <div class="col-md-12">
                                    <h4>
                                        Please <strong>save</strong> or <strong>delete</strong> the following note
                                        drafts:
                                    </h4>
                                </div>
                                <div class="col-md-8 col-md-offset-2">
                                    <table class="display dataTable no-footer">
                                        <thead>
                                        <tr>
                                            <th>
                                                Patient ID
                                            </th>
                                            <th>
                                                Date Created
                                            </th>
                                            <th>

                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($draftNotes as $key => $note)
                                            <tr>
                                                <td>
                                                    {{$note->patient_id}}
                                                </td>
                                                <td>
                                                    {{$note->performed_at->toDateString()}}
                                                </td>
                                                <td>
                                                    <a href="{{$note->editLink()}}"
                                                       style="color: blue; text-transform: uppercase; font-weight: 600">
                                                        Approve/Delete
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

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
                                                    {!! Form::select('filterStatus', array('all' => 'See All', 'scheduled' => 'Scheduled', 'completed' => 'Completed'), $dropdownStatus, $dropdownStatusClass) !!}
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
                                        {!! Form::close() !!}
                                    </div>
                                </div>
                                <div class="">
                                    @include('core::partials.errors.errors')
                                    @include('core::partials.errors.messages')
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
                                            <th>BHI<br>Time to date</th>
                                            <th># Calls<br>to date</th>
                                            <th>Provider</th>
                                            <th>Practice</th>
                                        </tr>
                                        </thead>
                                        <tbody>

                                        @if (count($calls) > 0)
                                            @foreach($calls as $key => $call)
                                                <?php
                                                $curTime = \Carbon\Carbon::now();

                                                $curTime   = $curTime->toTimeString();
                                                $rowBg     = '';
                                                $boldRow   = '';
                                                $textBlack = '';
                                                if ($call->scheduled_date == now()->toDateString()
                                                    && ! empty($call->call_time_end)
                                                    && now()
                                                        ->setTimezone($call->timezone ?? config('app.timezone'))
                                                        ->setTimeFromTimeString($call->call_time_end)->isPast()
                                                    && 'addendum_response' !== $call->type) {
                                                    $rowBg = 'background-color: rgba(255, 0, 0, 0.4);';
                                                }
                                                if (($call->asap || 'Call Back' === $call->type) && \CircleLinkHealth\SharedModels\Entities\Call::REACHED !== $call->status && \CircleLinkHealth\SharedModels\Entities\Call::DONE !== $call->status) {
                                                    $boldRow   = 'bold-row';
                                                    $textBlack = 'color:black;';
                                                }

                                                $route = route(
                                                    'patient.note.index',
                                                    ['patientId' => $call->patient_id]
                                                );

                                                if ('addendum_response' === $call->type) {
                                                    $route = route(
                                                        'redirect.readonly.activity',
                                                        ['callId' => $call->id]
                                                    );
                                                }
                                                ?>
                                                <tr class="{{$boldRow}}" style="{{ $rowBg . $textBlack }}">
                                                    <td class="vert-align" style="text-align:center">
                                                        @if(empty($call->type) || $call->type === 'call')
                                                            <i class="fas fa-phone"></i>
                                                        @elseif ($call->type === 'Call Back')
                                                            <img style="text-align: center"
                                                                 src="img/callback_image.svg"
                                                                 alt="callback image">
                                                        @elseif ($call->type === 'addendum_response')
                                                            <img style="text-align: center"
                                                                 src="img/addendum_image.svg"
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
                                                        <a href="{{ $route }}"
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

                                                    @if($call->asap === 1 && $call->status !== \CircleLinkHealth\SharedModels\Entities\Call::REACHED && $call->status !== \CircleLinkHealth\SharedModels\Entities\Call::DONE)
                                                        <td>{{ 'ASAP' }}</td>
                                                        <td>{{ 'N/A' }}</td>
                                                    @else
                                                        @if($call->type !== 'addendum_response')
                                                            <td>{{ $call->call_time_start }}</td>
                                                            <td>{{ $call->call_time_end }}</td>
                                                        @else
                                                            <td>{{ 'N/A' }}</td>
                                                            <td>{{ 'N/A' }}</td>
                                                        @endif
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

    <!-- call attempt_note modals -->
    @if (count($calls) > 0)
        @foreach($calls as $call)
            <?php
            $route      = route('patient.note.index', ['patientId' => $call->patient_id]);
            $buttonName = 'Continue to notes';

            if ('addendum_response' === $call->type) {
                $route      = route('redirect.readonly.activity', ['callId' => $call->id]);
                $buttonName = 'Continue to note';
            }
            ?>
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
                                <a href="{{$route}}"
                                   class="btn btn-primary">{{$buttonName}}</a>
                            </div>
                        </div>

                    </div>
                </div>
            @endif
        @endforeach
    @endif
@stop
