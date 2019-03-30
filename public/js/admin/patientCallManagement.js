$(document).ready(function(){
    // $("#dtBox").DateTimePicker();
    // $("#tBox").DateTimePicker();

    $('#addCallPatientId').select2();
    $('#addCallNurseId').select2();

});

$(function () {
    // Setup - add a text input to each footer cell
    $('#calls-table thead th').each(function () {
        if (!$(this).hasClass('nosearch')) {
            var title = $(this).text();
            $(this).html(title + '<br /><input style="width:100%;margin:0;padding:0;" type="text" placeholder="Search" />');
        }
    });

    //https://datatables.net/reference/api/
    var callstable = $('#calls-table').DataTable({
        //dom: 'Bfrtip',
        //buttons: [
        //    'copyHtml5',
        //   'excelHtml5',
        //    'csvHtml5',
        //    'pdfHtml5'
        //],
        "order": [[4, "asc"]],
        "iDisplayLength": 100,
        scrollX: true,
        fixedHeader: true,
        processing: true,
        serverSide: true,
        ajax: datatableDataUri,
        columns: [
            {
                "className": 'details-control', "data": 'blank', searchable: false, sortable: false
            },
            {data: 'call_id', name: 'call_id'},
            {data: 'nurse_name', name: 'nurse_name'},
            {data: 'patient_name', name: 'patient_name'},
            {data: 'ccm_complex', name: 'ccm_complex'},
            {data: 'program_name', name: 'program_name'},
            {data: 'no_call_attempts_since_last_success', name: 'no_call_attempts_since_last_success'},
            {data: 'scheduled_date', name: 'scheduled_date'},
            {data: 'window_start', name: 'window_start'},
            {data: 'window_end', name: 'window_end'},
            {data: 'patient_timezone', name: 'patient_timezone'},
            {data: 'patient_call_window_days_short', name: 'patient_call_window_days_short'},
            {data: 'last_contact_time', name: 'last_contact_time'},
            {data: 'cur_month_activity_time', name: 'cur_month_activity_time', searchable: false},
            //{data: 'no_of_calls', name: 'no_of_calls'},
            {data: 'no_of_successful_calls', name: 'no_of_successful_calls'},
            {data: 'ccm_status', name: 'ccm_status'},
            {data: 'billing_provider', name: 'billing_provider'},
            {data: 'birth_date', name: 'birth_date'},
            {data: 'scheduler', name: 'scheduler'},
        ],
        "fnRowCallback": function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            //console.log(aData);
            if (aData['background_color'] != '') {
                $('td', nRow).css('background-color', aData['background_color']);
            }
        }
    });

    /* Formatting function for row details - modify as you need */
    function format(d) {
        // `d` is the original data object for the row
        return '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">' +
            '<tr>' +
            '<td>General Comment:</td>' +
            '<td>' + d.general_comment_html + '</td>' +
            '</tr>' +
            '<tr>' +
            '<td>Attempt Note:</td>' +
            '<td>' + d.attempt_note_html + '</td>' +
            '</tr>' +
            '<tr>' +
            '<td>Last 3 ' + d.notes_link + ':</td>' +
            '<td>' + d.notes_html + '</td>' +
            '</tr>' +
            '<td>Call Windows:</td>' +
            '<td>' + d.patient_call_windows + '</td>' +
            '</tr>' +
            '</table>';
    }

    // Add event listener for opening and closing details
    $('#calls-table tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var row = callstable.row(tr);

        if (row.child.isShown()) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
        }
        else {
            // Open this row
            row.child(format(row.data())).show();
            tr.addClass('shown');
        }
    });

    // Apply the search
    callstable.columns().every(function () {
        var that = this;

        $('input', this.header()).on('keyup change', function () {
            if (that.search() !== this.value) {
                that
                    .search(this.value)
                    .draw();
            }
        });
    });

    $('#addCallButton').on("click", function () {
        var d = new Date();
        var month = d.getMonth() + 1;
        var day = d.getDate();
        var datetime = d.getFullYear() + '-' +
            (month < 10 ? '0' : '') + month + '-' +
            (day < 10 ? '0' : '') + day;
        $('#addCallPatientId').val('').trigger("change");
        $('#addCallNurseId').val('unassigned').trigger("change");
        $('input').val('');
        $('select').val('');
        $('#addCallDate').val(datetime);
        $('#addCallWindowStart').val('09:00');
        $('#addCallWindowEnd').val('17:00');
        $('#addCallErrorMsg').html('');
    });

    // add call
    $('#addCallModalYes').on("click", function () {

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: $('#addCallForm').attr('method'),
            url: $('#addCallForm').attr('action'),
            data: $('#addCallForm').serialize(),
            success: function (data) {
                //alert('call successfully added');
                $('#addCallModal').modal('hide');
            },
            error: function (data) {
                console.log(data.responseText);
                var parsedJson = jQuery.parseJSON(data.responseText);
                console.log(parsedJson);
                errorString = '<div class="alert alert-danger"><ul>';
                $.each(parsedJson.errors, function (key, value) {
                    errorString += '<li>' + value + '</li>';
                });
                errorString += '</ul></div>';
                $('#addCallErrorMsg').html(errorString);
            }
        });

        //https://datatables.net/reference/api/draw()
        callstable.draw(false);
    });


    // IDLE TIMER
    $(document).ready(function () {
        var consoleDebug = true; // debug toggle
        var idleTime = 40000; // ms before modal display (60000 = 1min)

        $(document).idleTimer(idleTime);

        // idleTimer ^
        $(document).on("idle.idleTimer", function (event, elem, obj) {
            if (consoleDebug) console.log('idleTimer hit!');
            if (!cpmEditableStatus) {
                if (consoleDebug) console.log('redraw the table..');
                callstable.draw();
            } else {
                if (consoleDebug) console.log('in line editing open, dont redraw.');
            }
            if (consoleDebug) console.log('reset idleTimer');
            $(document).idleTimer(idleTime);
        });
    });


    // vars
    var consoleDebug = true;
    var cpmEditableStatus = false;
    var cpmEditableCallId = false;
    var cpmEditableColumnName = false;
    var cpmEditableColumnValue = false;
    var cpmEditableColumnDisplayText = false;
    var cpmEditableTd = false;

    // edit action
    $('#calls-table').on('click', '.cpm-editable-icon', function () {
        if (cpmEditableStatus === true) {
            alert('already editing');
            return false;
            //saveEditableField();
        }
        cpmEditableCallId = $(this).attr('call-id');
        cpmEditableColumnName = $(this).attr('column-name');
        cpmEditableColumnValue = $(this).attr('column-value');
        cpmEditableColumnDisplayText = $(this).attr('column-value');
        cpmEditableTd = $(this).parent().parent();
        openEditableField();
        return false;
    });

    // save action
    $('#calls-table').on('click', '#cpm-editable-save', function () {
        cpmEditableColumnValue = $('#editableInput').val();
        cpmEditableColumnDisplayText = $('#editableInput').val();
        if (cpmEditableColumnName == 'outbound_cpm_id') {
            cpmEditableColumnDisplayText = $("#editableInput option:selected").text();
        }
        saveEditableField();
        return false;
    });

    // open editable field function
    function openEditableField() {
        cpmEditableStatus = true;
        if (cpmEditableColumnName == 'outbound_cpm_id') {
            //alert( cpmEditableColumnValue );
            var html = $('#nurseFormWrapper').html() + ' <a href="#" id="cpm-editable-save"><span class="glyphicon glyphicon-ok" style=""></span></a>';
            $(cpmEditableTd).html(html);
            $(".nurseFormSelect").each(function (index, element) {
                // get second one, skip first template in hidden div one
                if (index == 1) {
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
        } else if (cpmEditableColumnName == 'attempt_note') {
            $(cpmEditableTd).html('<textarea id="editableInput" style="width:300px;height:50px;" class="" name="editableInput" type="editableInput">' + cpmEditableColumnValue + '</textarea> &nbsp;<a href="#" id="cpm-editable-save"><span class="glyphicon glyphicon-ok" style=""></span></a>');
        } else if (cpmEditableColumnName == 'general_comment') {
            $(cpmEditableTd).html('<textarea id="editableInput" style="width:300px;height:50px;" class="" name="editableInput" type="editableInput">' + cpmEditableColumnValue + '</textarea> &nbsp;<a href="#" id="cpm-editable-save"><span class="glyphicon glyphicon-ok" style=""></span></a>');
        } else if (cpmEditableColumnName == 'scheduled_date') {
            $(cpmEditableTd).html('<input id="editableInput" style="width:100px;" class="" name="editableInput" type="input" value="' + cpmEditableColumnValue + '"  data-field="date" data-format="yyyy-MM-dd" /> &nbsp;<a href="#" id="cpm-editable-save"><span class="glyphicon glyphicon-ok" style=""></span></a>');
        } else if (cpmEditableColumnName == 'window_start' || cpmEditableColumnName == 'window_end') {
            $(cpmEditableTd).html('<input id="editableInput" style="width:50px;" class="" name="editableInput" type="input" value="' + cpmEditableColumnValue + '"  data-field="time" data-format="HH:mm" /> &nbsp;<a href="#" id="cpm-editable-save"><span class="glyphicon glyphicon-ok" style=""></span></a>');
        }
        return false;
    }

    // save editable field function
    function saveEditableField() {
        $(cpmEditableTd).html('<a href="#"><span class="cpm-editable-icon" call-id="' + cpmEditableCallId + '" column-name="' + cpmEditableColumnName + '" column-value="' + cpmEditableColumnValue + '">' + cpmEditableColumnDisplayText + '</span></a>');

        $(cpmEditableTd).addClass('highlight');
        setTimeout(function () {
            $(cpmEditableTd).removeClass('highlight');
            cpmEditableStatus = false;
        }, 1000);

        var data = {
            "callId": cpmEditableCallId,
            "columnName": cpmEditableColumnName,
            "value": cpmEditableColumnValue
        };
        if (consoleDebug) console.log(data);
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: "POST",
            url: callUpdatePostUri,
            data: data,
            //cache: false,
            encode: true,
            //processData: false,
            success: function (data) {
                // redraw if needed
                if (cpmEditableColumnName == 'attempt_note' || cpmEditableColumnName == 'general_comment') {
                    callstable.draw();
                }
            }
        });
        return false;
    }

    // initiate tooltips
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    });
});