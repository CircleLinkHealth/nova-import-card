$(document).ready(function(){
    /*
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
    */
    $("#dtBox").DateTimePicker();
});

$(document).ready(function(){
    $( "a, .submitFormBtn").click(function(e) {
        if ($(this).attr('omitsubmit')) {
            if(typeof $(this).attr('dtarget') === 'undefined') {
                return true; // no redirect dtarget
            } else {
                if ($(this).attr('dtarget')) {
                    var dtarget = $(this).attr('dtarget');
                } else if ($(this).attr('href')) {
                    var dtarget = $(this).attr('href');
                }
                if(dtarget) {
                    window.location.href = dtarget;
                }
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

    // http://stackoverflow.com/questions/30273155/dynamic-dropdowns-using-select2-json-request-and-laravel

    $(".patient2").select2({
        ajax: {
            url: "/ajax/patients/",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function (data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.items,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
        minimumInputLength: 1
    });
});