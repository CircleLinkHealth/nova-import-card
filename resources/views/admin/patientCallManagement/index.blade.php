@extends('partials.adminUI')

@section('content')
    <script type="text/javascript" src="{{ asset('/js/admin/patientCallManagement.js') }}"></script>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="//code.jquery.com/jquery-1.12.3.js"></script>
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
        $(document).ready(function() {
            // vars
            var consoleDebug = true;
            var cpmEditableStatus = false;
            var cpmEditableCallId = false;
            var cpmEditableColumnName = false;
            var cpmEditableColumnValue = false;
            var cpmEditableTd = false;

            // edit action
            $('#calls-table').on('click', '.cpm-editable-icon', function(){
                if(cpmEditableStatus === true) {
                    alert('already editing');
                    return false;
                    //saveEditableField();
                }
                cpmEditableCallId = $( this ).attr('call-id');
                cpmEditableColumnName = $( this ).attr('column-name');
                cpmEditableColumnValue = $( this ).attr('column-value');
                cpmEditableTd = $( this ).parent().parent();
                openEditableField();
                return false;
            });

            // save action
            $('#calls-table').on('click', '#cpm-editable-save', function(){
                cpmEditableColumnValue = $('#editableInput').val();
                saveEditableField();
                return false;
            });

            // open editable field function
            function openEditableField() {
                cpmEditableStatus = true;
                if(cpmEditableColumnName == 'outbound_cpm_id') {
                    alert( $('[name=nurseFormSelect]').val() );
                    $('[name=nurseFormSelect]').val( cpmEditableColumnValue );
                    alert( $('[name=nurseFormSelect]').val() );
                    var html = $('#nurseFormWrapper').html() + ' <a href="#" id="cpm-editable-save"><span class="glyphicon glyphicon-ok" style=""></span></a>';
                    $(cpmEditableTd).html(html);
                } else if(cpmEditableColumnName == 'call_date') {
                    $(cpmEditableTd).html('<input id="editableInput" style="width:100px;" class="" name="date" type="editableInput" value="' + cpmEditableColumnValue + '"  data-field="date" data-format="yyyy-MM-dd" /> &nbsp;<a href="#" id="cpm-editable-save"><span class="glyphicon glyphicon-ok" style=""></span></a>');
                } else if(cpmEditableColumnName == 'window_start' || cpmEditableColumnName == 'window_end') {
                    $(cpmEditableTd).html('<input id="editableInput" style="width:50px;" class="" name="editableInput" type="input" value="' + cpmEditableColumnValue + '"  data-field="time" data-format="HH:mm" /> &nbsp;<a href="#" id="cpm-editable-save"><span class="glyphicon glyphicon-ok" style=""></span></a>');
                }
                return false;
            }

            // save editable field function
            function saveEditableField() {
                $( cpmEditableTd ).html('<a href="#"><span class="cpm-editable-icon" call-id="' + cpmEditableCallId + '" column-name="' + cpmEditableColumnName + '" column-value="' + cpmEditableColumnValue + '">' + cpmEditableColumnValue + '</span></a>');

                $( cpmEditableTd ).addClass('highlight');
                setTimeout(function(){
                    $( cpmEditableTd ).removeClass('highlight');
                    cpmEditableStatus = false;
                }, 1000);

                var data = {
                    "callId": cpmEditableCallId,
                    "columnName": cpmEditableColumnName,
                    "value": cpmEditableColumnValue
                };
                if (consoleDebug) console.log(data);
                $.ajax({
                    type: "POST",
                    url: '<?php echo URL::route('api.callupdate'); ?>',
                    data: data,
                    //cache: false,
                    encode: true,
                    //processData: false,
                    success: function (data) {
                        // do something to signify success
                    }
                });
                return false;
            }
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


                        {!! Form::open(array('url' => URL::route('admin.patientCallManagement.index', array()), 'method' => 'get', 'class' => 'form-horizontal')) !!}

                                        <!--<td><input type="checkbox" name="calls[]" value="$call->id"></td> -->
                        <table class="" width="100%" cellspacing="0" id="calls-table">
                            <thead>
                            <tr>
                                <th></th>
                                <th style="width:50px;"></th>
                                <th>Status</th>
                                <th>Patient</th>
                                <th>DOB</th>
                                <th>Next Call Date</th>
                                <th>Window Start</th>
                                <th>Window End</th>
                                <th>Nurse</th>
                                <th>CCM Time</th>
                                <th>Last call</th>
                                <th># Total Calls</th>
                                <th># Successfull Calls</th>
                                <!--<th>Billing Provider</th>-->
                                <th>Patient Status</th>
                                <th>Billing Provider</th>
                                <th>Program</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th></th>
                                <th style="width:50px;"></th>
                                <th>Status</th>
                                <th>Patient</th>
                                <th>DOB</th>
                                <th>Next Call Date</th>
                                <th>Window Start</th>
                                <th>Window End</th>
                                <th>Nurse</th>
                                <th>CCM Time</th>
                                <th>Last call</th>
                                <th># Total Calls</th>
                                <th># Successfull Calls</th>
                                <th>Patient Status</th>
                                <th>Billing Provider</th>
                                <th>Program</th>
                            </tr>
                            </tfoot>
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
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>




    <script>

        $(function() {
            // Setup - add a text input to each footer cell
            $('#calls-table tfoot th').each( function () {
                var title = $(this).text();
                $(this).html( '<input style="width:100%;margin:0;padding:0;" type="text" placeholder="Search" />' );
            } );


            var callstable = $('#calls-table').DataTable({
                //dom: 'Bfrtip',
                //buttons: [
                //    'copyHtml5',
                //   'excelHtml5',
                //    'csvHtml5',
                //    'pdfHtml5'
                //],
                scrollX: true,
                processing: true,
                serverSide: true,
                ajax: '{{ route('datatables.anyDataCalls') }}',
                columns: [
                    {
                        "className":      'details-control', "data":           'blank', searchable: false, sortable: false
                    },
                    {data: 'call_id', name: 'call_id'},
                    {data: 'status', name: 'status'},
                    {data: 'patient_name', name: 'patient_name'},
                    {data: 'birth_date', name: 'birth_date'},
                    {data: 'call_date', name: 'call_date'},
                    {data: 'window_start', name: 'window_start'},
                    {data: 'window_end', name: 'window_end'},
                    {data: 'nurse_name', name: 'nurse_name'},
                    {data: 'cur_month_activity_time', name: 'cur_month_activity_time'},
                    {data: 'last_successful_contact_time', name: 'last_successful_contact_time'},
                    {data: 'no_of_calls', name: 'no_of_calls'},
                    {data: 'no_of_successful_calls', name: 'no_of_successful_calls'},
                    {data: 'ccm_status', name: 'ccm_status'},
                    {data: 'billing_provider', name: 'billing_provider'},
                    {data: 'program_name', name: 'program_name'},
                ]
            });

            /* Formatting function for row details - modify as you need */
            function format ( d ) {
                // `d` is the original data object for the row
                return '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">'+
                        '<tr>'+
                        '<td>Note Type:</td>'+
                        '<td>'+d.note_type+'</td>'+
                        '</tr>'+
                        '<tr>'+
                        '<td>Note:</td>'+
                        '<td>'+d.note_body+'</td>'+
                        '</tr>'+
                        '<td>Call Windows:</td>'+
                        '<td>'+d.patient_call_windows+'</td>'+
                        '</tr>'+
                        '<tr>'+
                        '<tr>'+
                        '<td>Extra info:</td>'+
                        '<td>And any further details here...</td>'+
                        '</tr>'+
                        '</table>';
            }

            // Add event listener for opening and closing details
            $('#calls-table tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = callstable.row( tr );

                if ( row.child.isShown() ) {
                    // This row is already open - close it
                    row.child.hide();
                    tr.removeClass('shown');
                }
                else {
                    // Open this row
                    row.child( format(row.data()) ).show();
                    tr.addClass('shown');
                }
            } );

            // Apply the search
            callstable.columns().every( function () {
                var that = this;

                $( 'input', this.footer() ).on( 'keyup change', function () {
                    if ( that.search() !== this.value ) {
                        that
                                .search( this.value )
                                .draw();
                    }
                } );
            } );
        });
    </script>
@stop