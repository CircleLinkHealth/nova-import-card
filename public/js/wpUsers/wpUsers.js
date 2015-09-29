function addCondition() {
    var conditionshtml = $('#jsconditions').html();
    $('#conditions').append(conditionshtml);
    //$( ".condition" ).last().find( '[name="value"]').val('test');
    //$( '[name="value"]' ).val('hohoh');
    //$( '.c-value:last' ).val( "red" );
    $( '.c-value' ).last().css( "background-color", "red" );
    //alert( $( ".condition" ).last().find( '[name="value"]').val() );
    //alert( $( 'input[name="condition"]:last' ).val() );
    return false;
}

function addAction() {
    var actionshtml = $('#jsactions').html();
    $('#actions').append(actionshtml);
    return false;
}

$(document).ready(function(){

    // select2
    $('#filterRole').select2();
    $('#filterProgram').select2();
    $('#filterUser').select2();

    //alert('bump');
    $( "#active_date" ).datetimepicker({
        dateFormat: "yy-mm-dd",
        timeFormat: "hh:mm:ss"
    });
    $( "#preferred_contact_time" ).timepicker({
        timeFormat: "hh:mm TT"
    });
    $( "#daily_reminder_time" ).timepicker({
        timeFormat: "hh:mm"
    });
    $( "#hospital_reminder_time" ).timepicker({
        timeFormat: "hh:mm"
    });
    $( "#birth_date" ).datepicker({
        dateFormat: "yy-mm-dd"
    });
    $( "#consent_date" ).datepicker({
        dateFormat: "yy-mm-dd"
    });
    $( "#registration_date" ).datetimepicker({
        dateFormat: "yy-mm-dd",
        timeFormat: "hh:mm:ss"
    });

    $('body').on('click', '.add-condition', function(event) {
        event.preventDefault();
        addCondition();
        return false;
    });

    $('body').on('click', '.add-action', function(event) {
        event.preventDefault();
        addAction();
        return false;
    });
});