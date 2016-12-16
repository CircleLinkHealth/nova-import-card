@extends('layouts.master')

@section('content')
    <script type="text/javascript" src="{{ asset('/js/admin/patientCallManagement.js') }}"></script>
    <!-- JQuery -->
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
                } else if(cpmEditableColumnName == 'scheduled_date') {
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
    <h1>CALLS</h1>
    <table class="" width="100%" cellspacing="0" id="calls-table">
        <thead>
        <tr>
            <th style="width:50px;"></th>
            <th>Status</th>
            <th>Patient</th>
            <th>DOB</th>
            <th>Next Call Date</th>
            <th>Window Start</th>
            <th>Window End</th>
            <th>Nurse</th>
            <th>CCM Time</th>
            <th># Calls to date</th>
            <th>Last call</th>
            <!--<th>Billing Provider</th>-->
            <th>Patient Status</th>
            <th>Billing Provider</th>
            <th>Practice</th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <th style="width:50px;"></th>
            <th>Status</th>
            <th>Patient</th>
            <th>DOB</th>
            <th>Next Call Date</th>
            <th>Window Start</th>
            <th>Window End</th>
            <th>Nurse</th>
            <th>CCM Time</th>
            <th># Calls to date</th>
            <th>Last call</th>
            <th>Patient Status</th>
            <th>Billing Provider</th>
            <th>Practice</th>
        </tr>
        </tfoot>
    </table>

    <h1>USERS</h1>
    <table class="table table-bordered" id="users-table">
        <thead>
        <tr>
            <th>Id</th>
            <th>Name</th>
            <th>Email</th>
            <th>Created At</th>
            <th>Updated At</th>
        </tr>
        </thead>
    </table>
@stop

@push('scripts')
<script>
    $(function() {
        $('#users-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('datatables.data') }}',
                columns: [
                    {data: 0, name: 'ID'},
                    {data: 1, name: 'display_name'},
                    {data: 2, name: 'email'},
                    {data: 3, name: 'created_at'},
                    {data: 4, name: 'updated_at'}
        ]
        });
    });

    $(function() {
        // Setup - add a text input to each footer cell
        $('#calls-table tfoot th').each( function () {
            var title = $(this).text();
            $(this).html( '<input style="width:100%;margin:0;padding:0;" type="text" placeholder="Search" />' );
        } );


        var callstable = $('#calls-table').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'copyHtml5',
                'excelHtml5',
                'csvHtml5',
                'pdfHtml5'
            ],
            scrollX: true,
            processing: true,
            serverSide: true,
            ajax: '{{ route('datatables.anyDataCalls') }}',
            columns: [
                {
                    "className":      'details-control', "data":           'blank', searchable: false, sortable: false
                },
                {data: 'status', name: 'status'},
                {data: 'patient_name', name: 'patient_name'},
                {data: 'birth_date', name: 'birth_date'},
                {data: 'scheduled_date', name: 'scheduled_date'},
                {data: 'window_start', name: 'window_start'},
                {data: 'window_end', name: 'window_end'},
                {data: 'nurse_name', name: 'nurse_name'},
                {data: 'cur_month_activity_time', name: 'cur_month_activity_time'},
                {data: 'cur_month_activity_time', name: 'cur_month_activity_time'},
                {data: 'last_successful_contact_time', name: 'last_successful_contact_time', searchable: false, sortable: false},
                {data: 'ccm_status', name: 'ccm_status'},
                {data: 'program_name', name: 'program_name'},
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
@endpush
