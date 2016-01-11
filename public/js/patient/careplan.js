$(document).ready(function(){
    $('#preferred_contact_time').timepicker( {
        timeFormat: 'hh:mm TT',
        stepMinute: 15
    });

    $('#birth_date').datepicker({
        dateFormat: "yy-mm-dd"
    });

    $('#consent_date').datepicker({
        dateFormat: "yy-mm-dd"
    });
});

$(document).ready(function(){
    /* $( ".submitFormBtn, a").click(function(e) { */
    $( ".submitFormBtn").click(function(e) {
        if ($(this).attr('omitsubmit')) {
            if(typeof $(this).attr('dtarget') === 'undefined') {
                return true; // no redirect dtarget
            } else {
                window.location.href = $(this).attr('dtarget');
                return true;
            }
        }
        e.preventDefault();
        if ($(this).attr('dtarget')) {
            var dtarget = $(this).attr('dtarget');
        } else if ($(this).attr('href')) {
            var dtarget = $(this).attr('href');
        }
        if(dtarget) {
            $('<input>').attr({
                type: 'hidden',
                id: 'direction',
                name: 'direction',
                value: dtarget
            }).appendTo('form#ucpForm');
            $('form#ucpForm').submit();
        }
        return false;
    });

    $( ".itemTrigger" ).click(function(e) {
        id = $(this).attr('id');
        if($(this).is(':checked'))
            $("#" + id + "Detail").removeAttr('style');  // checked
        else
            $("#" + id + "Detail").hide();  // unchecked
    });
});