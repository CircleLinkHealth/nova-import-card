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
    //alert('bump');
    $( "#active_date" ).datetimepicker();
    $( "#preferred_contact_time" ).timepicker();
    $( "#daily_reminder_time" ).timepicker();
    $( "#hospital_reminder_time" ).timepicker();
    $( "#birth_date" ).datepicker();
    $( "#consent_date" ).datepicker();

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