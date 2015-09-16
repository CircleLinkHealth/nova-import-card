$(document).ready(function(){
    $('#preferred_contact_time').timepicker( {
        timeFormat: 'hh:mm TT',
        stepMinute: 15
    });

    $('#birth_date').datepicker({
        dateFormat: "yyyy-mm-dd"
    });

    $('#consent_date').datepicker({
        dateFormat: "yyyy-mm-dd"
    });
});