@extends('partials.adminUI')

@section('content')

    <?php

    $active_nurses  = (new App\Nurse())->activeNursesForUI();

    ?>
    <script type="text/javascript">
        var callUpdatePostUri = "{{ URL::route('api.callupdate') }}";
        var datatableDataUri = "{{ URL::route('datatables.anyCallsManagement') }}";
    </script>
    <script type="text/javascript" src="{{ asset('/js/admin/patientCallManagement.js') }}"></script>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
    <link href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css" rel="stylesheet">

    <script src="https://cdn.datatables.net/buttons/1.2.2/js/dataTables.buttons.min.js"></script>
    <link href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css" rel="stylesheet">
    <script src="//cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>
    <script src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
    <script src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
    <script src="//cdn.datatables.net/buttons/1.2.2/js/buttons.html5.min.js"></script>

    <script>
        $(document).ready(function () {
            $(".nurses").select2();

        });
    </script>

    <script>
        $(document).ready(function() {

        } );
    </script>
    <style>
        #calls-table tbody>tr>td {
            vertical-align: middle;
            white-space: nowrap;
        }

        .cpm-editable {
            color:#000;
        }

        .highlight {
            color:green;
            font-weight:bold;
        }
        td.details-control {
            color:#fff;
            background: url('{{ asset('/vendor/datatables-images/details_open.png') }}') no-repeat center center;
            cursor: pointer;
        }
        tr.shown td.details-control {
            background: url('{{ asset('/vendor/datatables-images/details_close.png') }}') no-repeat center center;
        }
    </style>


    <div id="nurseFormWrapper" style="display:none;">
        {!! Form::select('nurseFormSelect', array('unassigned' => 'Unassigned') + $active_nurses->all(), '', ['class' => 'nurses select-picker nurseFormSelect', 'style' => 'width:150px;']) !!}
    </div>

    <div class="modal fade" id="addCallModal" role="dialog" style="height: 10000px; opacity: 1;background-color: black">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                    <h4 class="modal-title" id="addCallModalLabel">Add New Call</h4>
                </div>
                <div class="modal-body">
                    <div class="row" style="margin:20px 0px 40px 0px;">
                        <form id="addCallForm" action="<?php echo URL::route('api.callcreate'); ?>" method="post">
                            {{ csrf_field() }}
                        <div class="col-md-10 col-md-offset-1">
                            <div class="row">
                                <div class="col-xs-12" id="addCallErrorMsg"></div>
                            </div>
                            <div class="row form-group">
                                <div class="col-xs-4 text-right">{!! Form::label('inbound_cpm_id', 'Patient:') !!}</div>
                                <div class="col-xs-8">{!! Form::select('inbound_cpm_id', array('' => '') + $patientList, '', ['id' => 'addCallPatientId', 'class' => 'form-control select-picker', 'style' => 'width:100%;']) !!}</div>
                            </div>

                            <div class="row form-group">
                                <div class="col-xs-4 text-right">{!! Form::label('outbound_cpm_id', 'Nurse:') !!}</div>
                                <div class="col-xs-8">{!! Form::select('outbound_cpm_id', array('unassigned' => 'Unassigned') + $active_nurses->all(), 'unassigned', ['id' => 'addCallNurseId', 'class' => 'form-control select-picker', 'style' => 'width:100%;']) !!}</div>
                            </div>
                            <div class="row form-group">
                                <div class="col-xs-4 text-right">{!! Form::label('scheduled_date', 'Date:') !!}</div>
                                <div class="col-xs-8">{!! Form::input('text', 'scheduled_date', '', ['id' => 'addCallDate', 'class' => 'form-control', 'style' => 'width:100%;', 'data-field' => "date", 'data-format' => "yyyy-MM-dd"]) !!}</div>
                            </div>
                            <div class="row form-group">
                                <div class="col-xs-4 text-right">{!! Form::label('window_start', 'Start Time:') !!}</div>
                                <div class="col-xs-8">{!! Form::input('text', 'window_start', '09:00', ['id' => 'addCallWindowStart', 'class' => 'form-control', 'style' => 'width:100%;', 'data-field' => "time", 'data-format' => "HH:mm"]) !!}</div>
                            </div>
                            <div class="row form-group">
                                <div class="col-xs-4 text-right">{!! Form::label('window_end', 'End Time:') !!}</div>
                                <div class="col-xs-8">{!! Form::input('text', 'window_end', '17:00', ['id' => 'addCallWindowEnd', 'class' => 'form-control', 'style' => 'width:100%;', 'data-field' => "time", 'data-format' => "HH:mm"]) !!}</div>
                            </div>
                            <div class="row form-group">
                                <div class="col-xs-4 text-right">{!! Form::label('attempt_note', 'Add Text:') !!}</div>
                                <div class="col-xs-8">{!! Form::input('textarea', 'attempt_note', '', ['class' => 'form-control', 'style' => 'width:100%;']) !!}</div>
                            </div>
                        </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="addCallModalNo" class="btn btn-warning" data-dismiss="modal">Cancel</button>
                    <button type="button" id="addCallModalYes" class="btn btn-success">Add Call</button>
                </div>
            </div>
        </div>
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
                                <a href="{{ URL::route('CallReportController.exportxls', array()) }}" class="btn btn-primary pull-right">Excel Export</a> &nbsp;
                                <button style="margin-right:5px;" type="button" id="addCallButton" class="btn btn-success pull-right" data-toggle="modal" data-target="#addCallModal">Add Call</button>
                            </div>
                        </div>
                        {!! Form::open(array('url' => URL::route('admin.patientCallManagement.index', array()), 'method' => 'get', 'class' => 'form-horizontal')) !!}
                        <div class="row" style="margin:40px 0px;">
                            <div class="col-xs-4">
                                With selected calls:&nbsp;&nbsp;
                                <select name="action">
                                    <option value="assign">Assign Nurse</option>
                                    <option value="delete">Delete Calls</option>
                                </select>
                            </div>
                            <div class="col-xs-6">Nurse:&nbsp;&nbsp;
                                {!! Form::select('assigned_nurse', array('unassigned' => 'Unassigned') + $active_nurses->all(), 'unassigned', ['class' => 'select-picker', 'style' => 'width:50%;']) !!}
                            </div>
                            <div class="col-xs-2">
                                <button type="submit" value="Submit" class="btn btn-primary btn-xs" style="margin-left:10px;"><i class="glyphicon glyphicon-circle-arrow-right"></i> Perform Action</button>
                            </div>
                        </div>
                        <table class="display" width="100%" cellspacing="0" id="calls-table">
                            <thead>
                            <tr>
                                <th class="nosearch"></th>
                                <th class="nosearch" style="width:50px;"></th>
                                <th>Nurse</th>
                                <th>Patient</th>
                                <th>Practice</th>
                                <th>Last Call Status</th>
                                <th>Next Call</th>
                                <th>Call Time Start</th>
                                <th>Call Time End</th>
                                <th>Time Zone</th>
                                <th>Preferred Call Days</th>
                                <th>Last Call</th>
                                <th class="nosearch">CCM Time</th>
                                <!-- <th>Total Calls</th> -->
                                <th>Successfull Calls</th>
                                <th>Patient Status</th>
                                <th>Billing Provider</th>
                                <th>DOB</th>
                                <th>Scheduler</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th></th>
                                <th style="width:50px;"></th>
                                <th>Nurse</th>
                                <th>Patient</th>
                                <th>Practice</th>
                                <th>Last Call Status</th>
                                <th>Next Call Date</th>
                                <th>Call Time Start</th>
                                <th>Call Time End</th>
                                <th>Time Zone</th>
                                <th>Preferred Call Days</th>
                                <th>Last Call</th>
                                <th>CCM Time</th>
                                <!-- <th>Total Calls</th> -->
                                <th>Successfull Calls</th>
                                <th>Patient Status</th>
                                <th>Billing Provider</th>
                                <th>DOB</th>
                                <th>Scheduler</th>
                            </tr>
                            </tfoot>
                        </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop