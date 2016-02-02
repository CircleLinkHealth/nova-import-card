$(document).ready(function(){
    var hasFormChanged = false;

    $('body').on('click', 'input:radio[name="ctlc"]', function(event) {
        hasFormChanged = true;
        return true;
    });
    $('body').on('click', 'input:radio[name="ctbp"]', function(event) {
        hasFormChanged = true;
        return true;
    });

    $( ".submitFormBtn, a").click(function(e) {
        // omit add new member link
        if( $(this).hasClass('addCareTeamMember') ) {
            return false;
        }
        // validation start
        if ( !$('input:radio[name="ctlc"]').is(':checked')) {
            // error modal
            $('#ctModalError').html('No Lead Contact has been selected. Please select a Lead Contact to continue.');
            $('#ctModal').modal();
            return false;
        }
        if ( !$('input:radio[name="ctbp"]').is(':checked')) {
            // error modal
            $('#ctModalError').html('No Billing Provider has been selected. Please select a Billing Provider to continue.');
            $('#ctModal').modal();
            return false;
        }

        // validation end
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
            if(hasFormChanged) {
                // confirm modal
                lc_id = $('input:radio[name="ctlc"]:checked').val();
                lc_name = $('#providerName' + lc_id).html();
                bp_id = $('input:radio[name="ctbp"]:checked').val();
                bp_name = $('#providerName' + bp_id).html();
                $('#ctConfModalError').html('Are you sure <strong>'+bp_name+' is the Billing Provider</strong> and <strong>'+lc_name+' is the Lead Contact</strong>?');
                $('#ctConfModal').modal();
                // yes/no button in modal
                $('#ctConfModalYes').on("click", function () {
                    $('<input>').attr({
                        type: 'hidden',
                        id: 'direction',
                        name: 'direction',
                        value: dtarget
                    }).appendTo('form#ucpForm');
                    $('form#ucpForm').submit();
                });
                return false;
            } else {
                $('<input>').attr({
                    type: 'hidden',
                    id: 'direction',
                    name: 'direction',
                    value: dtarget
                }).appendTo('form#ucpForm');
                $('form#ucpForm').submit();
            }
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