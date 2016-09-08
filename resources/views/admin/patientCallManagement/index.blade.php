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
            var cpmEditableColumnDisplayText = false;
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
                cpmEditableColumnDisplayText = $( this ).attr('column-value');
                cpmEditableTd = $( this ).parent().parent();
                openEditableField();
                return false;
            });

            // save action
            $('#calls-table').on('click', '#cpm-editable-save', function(){
                cpmEditableColumnValue = $('#editableInput').val();
                cpmEditableColumnDisplayText = $('#editableInput').val();
                if(cpmEditableColumnName == 'outbound_cpm_id') {
                    cpmEditableColumnDisplayText = $("#editableInput option:selected").text();
                }
                saveEditableField();
                return false;
            });

            // open editable field function
            function openEditableField() {
                cpmEditableStatus = true;
                if(cpmEditableColumnName == 'outbound_cpm_id') {
                    //alert( cpmEditableColumnValue );
                    var html = $('#nurseFormWrapper').html() + ' <a href="#" id="cpm-editable-save"><span class="glyphicon glyphicon-ok" style=""></span></a>';
                    $(cpmEditableTd).html(html);
                    $(".nurseFormSelect").each(function(index, element) {
                        // get second one, skip first template in hidden div one
                        if(index == 1) {
                            // set value
                            $(this).val(cpmEditableColumnValue);
                            console.log('element at index ' + index + 'is ' + (this.tagName));
                            console.log('element at index ' + index + 'is ' + (this.tagName));
                            console.log('current element as dom object:' + element);
                            console.log('current element as jQuery object:' + $(this));
                            $(this).attr('id', "editableInput");
                            console.log('current element id ==:' + $(this).attr('id'));
                        }
                    });
                } else if(cpmEditableColumnName == 'attempt_note') {
                    $(cpmEditableTd).html('<input id="editableInput" style="width:100px;" class="" name="editableInput" type="editableInput" value="' + cpmEditableColumnValue + '" /> &nbsp;<a href="#" id="cpm-editable-save"><span class="glyphicon glyphicon-ok" style=""></span></a>');
                } else if(cpmEditableColumnName == 'scheduled_date') {
                    $(cpmEditableTd).html('<input id="editableInput" style="width:100px;" class="" name="date" type="editableInput" value="' + cpmEditableColumnValue + '"  data-field="date" data-format="yyyy-MM-dd" /> &nbsp;<a href="#" id="cpm-editable-save"><span class="glyphicon glyphicon-ok" style=""></span></a>');
                } else if(cpmEditableColumnName == 'window_start' || cpmEditableColumnName == 'window_end') {
                    $(cpmEditableTd).html('<input id="editableInput" style="width:50px;" class="" name="editableInput" type="input" value="' + cpmEditableColumnValue + '"  data-field="time" data-format="HH:mm" /> &nbsp;<a href="#" id="cpm-editable-save"><span class="glyphicon glyphicon-ok" style=""></span></a>');
                }
                return false;
            }

            // save editable field function
            function saveEditableField() {
                $( cpmEditableTd ).html('<a href="#"><span class="cpm-editable-icon" call-id="' + cpmEditableCallId + '" column-name="' + cpmEditableColumnName + '" column-value="' + cpmEditableColumnValue + '">' + cpmEditableColumnDisplayText + '</span></a>');

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
        {!! Form::select('nurseFormSelect', array('unassigned' => 'Unassigned') + $nurses->all(), '', ['class' => 'select-picker nurseFormSelect', 'style' => 'width:150px;']) !!}
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
                                <a href="{{ URL::route('CallReportController.exportxls', array()) }}" class="btn btn-success pull-right">Excel Export</a>
                            </div>
                        </div>
                        {!! Form::open(array('url' => URL::route('admin.patientCallManagement.index', array()), 'method' => 'get', 'class' => 'form-horizontal')) !!}
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
                        <table class="display" width="100%" cellspacing="0" id="calls-table">
                            <thead>
                            <tr>
                                <th class="nosearch"></th>
                                <th class="nosearch" style="width:50px;"></th>
                                <th>Nurse</th>
                                <th>Patient</th>
                                <th>Program</th>
                                <th class="nosearch">Last Call Status</th>
                                <th>Next Call</th>
                                <th>Call Time Start</th>
                                <th>Call Time End</th>
                                <th>Preferred Call Days</th>
                                <th>Last Call</th>
                                <th class="nosearch">CCM Time</th>
                                <!-- <th>Total Calls</th> -->
                                <th>Successfull Calls</th>
                                <th>Patient Status</th>
                                <th>Billing Provider</th>
                                <th>DOB</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th></th>
                                <th style="width:50px;"></th>
                                <th>Nurse</th>
                                <th>Patient</th>
                                <th>Program</th>
                                <th>Last Call Status</th>
                                <th>Next Call Date</th>
                                <th>Call Time Start</th>
                                <th>Call Time End</th>
                                <th>Preferred Call Days</th>
                                <th>Last Call</th>
                                <th>CCM Time</th>
                                <!-- <th>Total Calls</th> -->
                                <th>Successfull Calls</th>
                                <th>Patient Status</th>
                                <th>Billing Provider</th>
                                <th>DOB</th>
                            </tr>
                            </tfoot>
                        </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>




    <script>

        $(function() {
            // Setup - add a text input to each footer cell
            $('#calls-table thead th').each( function () {
                if(!$(this).hasClass('nosearch')) {
                    var title = $(this).text();
                    $(this).html(title + '<br /><input style="width:100%;margin:0;padding:0;" type="text" placeholder="Search" />');
                }
            } );


            var callstable = $('#calls-table').DataTable({
                //dom: 'Bfrtip',
                //buttons: [
                //    'copyHtml5',
                //   'excelHtml5',
                //    'csvHtml5',
                //    'pdfHtml5'
                //],
                "order": [[ 4, "desc" ]],
                "iDisplayLength": 100,
                scrollX: true,
                fixedHeader: true,
                processing: true,
                serverSide: true,
                ajax: '{{ route('datatables.anyCallsManagement') }}',
                columns: [
                    {
                        "className":      'details-control', "data":           'blank', searchable: false, sortable: false
                    },
                    {data: 'call_id', name: 'call_id'},
                    {data: 'nurse_name', name: 'nurse_name'},
                    {data: 'patient_name', name: 'patient_name'},
                    {data: 'program_name', name: 'program_name'},
                    {data: 'no_call_attempts_since_last_success', name: 'no_call_attempts_since_last_success', searchable: false},
                    {data: 'scheduled_date', name: 'scheduled_date'},
                    {data: 'window_start', name: 'window_start'},
                    {data: 'window_end', name: 'window_end'},
                    {data: 'patient_call_window_days_short', name: 'patient_call_window_days_short'},
                    {data: 'last_contact_time', name: 'last_contact_time'},
                    {data: 'cur_month_activity_time', name: 'cur_month_activity_time', searchable: false},
                    //{data: 'no_of_calls', name: 'no_of_calls'},
                    {data: 'no_of_successful_calls', name: 'no_of_successful_calls'},
                    {data: 'ccm_status', name: 'ccm_status'},
                    {data: 'billing_provider', name: 'billing_provider'},
                    {data: 'birth_date', name: 'birth_date'},
                ],
                "fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
                    console.log(aData);
                    if ( aData['background_color'] != '' )
                    {
                        $('td', nRow).css('background-color', aData['background_color']);
                    }
                }
            });

            /* Formatting function for row details - modify as you need */
            function format ( d ) {
                // `d` is the original data object for the row
                return '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">'+
                        '<tr>'+
                        '<td>General Comment:</td>'+
                        '<td>'+d.general_comment+'</td>'+
                        '</tr>'+
                        '<tr>'+
                        '<td>Attempt Note:</td>'+
                        '<td>'+ d.attempt_note_html+'</td>'+
                        '</tr>'+
                        '<tr>'+
                        '<td>Last 3 ' + d.notes_link + ':</td>'+
                        '<td>'+d.notes_html+'</td>'+
                        '</tr>'+
                        '<td>Call Windows:</td>'+
                        '<td>'+d.patient_call_windows+'</td>'+
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

                $( 'input', this.header() ).on( 'keyup change', function () {
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