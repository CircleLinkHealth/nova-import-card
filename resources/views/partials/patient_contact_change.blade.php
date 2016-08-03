<div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12"
     style=" border-bottom:3px solid #50b2e2;padding: 10px 48px;">

    <div class="col-xs-12" style="">
        <input type="text" class="form-control" name="general_comment" id="general_comment"
               value="{{$patient->patientInfo->general_comment}}"
               placeholder="{{$patient->patientInfo->general_comment == '' ? 'Enter General Comment...' : $patient->patientInfo->general_comment}}"
               aria-describedby="sizing-addon2" style="margin: 0 auto; text-align: left; color: #333;">
    </div>

    <!-- The next div is the contact statement -->

    @if($window_flag)
    <div class="col-xs-12 inline-block row" style=" padding: 0px; width: 98%; text-align: center;">
        Call Times: <span id="start_window_text">{{Carbon\Carbon::parse($patient->patientInfo->daily_contact_window_start)->format('H:i')}}</span> to <span id="end_window_text">{{Carbon\Carbon::parse($patient->patientInfo->daily_contact_window_end)->format('H:i')}}</span>
        on <span id="days_text">{{\App\PatientInfo::numberToTextDaySwitcher($patient->patientInfo->preferred_cc_contact_days)}}</span>; <span id="frequency_text">{{$patient->patientInfo->preferred_calls_per_month}}</span>x Monthly
    @else
            <div class="col-xs-12 inline-block row" style=" padding: 0px; width: 98%; text-align: center;">
                <span style="color: red"><b> Please enter preferred call times, if known.</b></span>

    @endif



        <button type="" class="show_hide edit_button" href="#" rel="#slidingDiv">
            <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
        </button>


        <div class="form-block col-md-12">
            <div id="slidingDiv" class="" style="display: none; margin: 0 auto;
    text-align: left;border:0px; padding: 10px 35px;">
                <div class="row">
                    <label class="col-xs-4" style="padding-left: -1px;" for="contact_day">Contact Days</label>
                    <label class="col-xs-3" style="padding-left: 0px; left: 0px" for="window_start">Calls Start Time</label>
                    <label class="col-xs-3" style="padding-left: 0px; left: 0px" for="window_end">Calls End Time</label>
                    <label class="col-xs-1" style="left: -20px" for="frequency">Frequency</label>
                </div>

                <div class="col-xs-4" style="padding-left: 0px;">
                    <select id="days" name="days[]"
                            class="selectpicker dropdown Valid form-control"
                            data-size="7" style="width: 155px"
                            multiple>
                        <option value="1" {{in_array("1", explode(',',$patient->patientInfo->preferred_cc_contact_days)) ? "selected" : ''}}>Mon</option>
                        <option value="2" {{in_array(" 2",explode(',',$patient->patientInfo->preferred_cc_contact_days)) ? "selected" : ''}}>Tue</option>
                        <option value="3" {{in_array(" 3",explode(',',$patient->patientInfo->preferred_cc_contact_days)) ? "selected" : ''}}>Wed</option>
                        <option value="4" {{in_array(" 4",explode(',',$patient->patientInfo->preferred_cc_contact_days)) ? "selected" : ''}}>Thu</option>
                        <option value="5" {{in_array(" 5",explode(',',$patient->patientInfo->preferred_cc_contact_days)) ? "selected" : ''}}>Fri</option>
                        <option value="6" {{in_array(" 6",explode(',',$patient->patientInfo->preferred_cc_contact_days)) ? "selected" : ''}}>Sat</option>
                        <option value="7" {{in_array(" 7",explode(',',$patient->patientInfo->preferred_cc_contact_days)) ? "selected" : ''}}>Sun</option>
                    </select>
                </div>

                <div class="col-xs-3" style="padding-left: 0px;">
                    <input class="form-control" name="window_start" type="time"
                           value="{{$patient->patientInfo->getDailyContactWindowStartAttribute()}}"
                           id="window_start" placeholder="time">
                </div>

                <div class="col-xs-3" style="padding-left: 0px;">
                    <input class="form-control" name="window_end" type="time"
                           value="{{$patient->patientInfo->getDailyContactWindowEndAttribute()}}"
                           id="window_end" placeholder="time">
                </div>
                <div class="col-xs-2" style="padding-left: 0px; padding-bottom: 3px">
                    <select id="frequency" name="frequency"
                            class="selectpickerX dropdown Valid form-control" data-size="2"
                            style="width: 150px" >
                        <option value="1" {{$patient->patientInfo->preferred_calls_per_month == 1 ? 'selected' : ''}}> 1x Monthly</option>
                        <option value="2" {{$patient->patientInfo->preferred_calls_per_month == 2 ? 'selected' : ''}}> 2x Monthly</option>
                        <option value="3" {{$patient->patientInfo->preferred_calls_per_month == 3 ? 'selected' : ''}}> 3x Monthly</option>
                        <option value="4" {{$patient->patientInfo->preferred_calls_per_month == 4 ? 'selected' : ''}}> 4x Monthly</option>
                    </select>
                </div>

                @if($window_flag)
                <div class="col-xs-12" style="color: red; text-align: center">
                    <b>*Please save the entire note to update call times</b>
                </div>
                @else
                    <div class="col-xs-12" style="text-align: center">
                        <b>*Saving note will create call times</b>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

<script>

    $(document).ready(function() {
        $("#days").change(function() {

            var countries = [];

            $.each($("#days option:selected"), function(){
                countries.push($(this).html());
            });

            $('#days_text').html(countries.join(', '));

        }).change();

    });

    $(document).ready(function() {
        $("#window_start").change(function() {

            $('#start_window_text').html(parseTime($(this).val()));
        }).change();
    });

    $(document).ready(function() {
        $("#window_end").change(function() {
            $('#end_window_text').html(parseTime($(this).val()));
        }).change();
    });

    $(document).ready(function() {
        $("#frequency").change(function() {
            $('#frequency_text').html($(this).val());
        }).change();
    });

    function parseTime(timeString)
    {
        if (timeString == '') return null;
        var d = new Date();
        var time = timeString.match(/(\d+)(:(\d\d))?\s*(p?)/i);
        d.setHours( parseInt(time[1],10) + ( ( parseInt(time[1],10) < 12 && time[4] ) ? 12 : 0) );
        d.setMinutes( parseInt(time[3],10) || 0 );
        d.setSeconds(0, 0);
        return ('0' + d.getHours()).slice(-2) + ":" + ('0' + d.getMinutes()).slice(-2)

    }

    // Script for editing the patient times

    $(document).ready(function () {


        $('.show_hide').showHide({
            speed: 1000, // speed you want the toggle to happen
            easing: '', // the animation effect you want. Remove this line if you dont want an effect and if you haven't included jQuery UI
            changeText: 1, // if you dont want the button text to change, set this to 0
            showText: "<span class=\"glyphicon glyphicon-pencil\" aria-hidden=\"true\"></span>", //the button text to show when a div is closed
            hideText: "<div style='color: #47beab;'> Hide* </div>" // the button text to show when a div is open

        });


    });

    (function ($) {
        $.fn.showHide = function (options) {

            //default vars for the plugin
            var defaults = {
                speed: 2000,
                easing: '',
                changeText: 0,
                showText: 'Hide',
                hideText: '(changes saved on note submission)'

            };
            var options = $.extend(defaults, options);

            $(this).click(function () {
                // optionally add the class .toggleDiv to each div you want to automatically close
                $('.toggleDiv').slideUp(options.speed, options.easing);
                // this var stores which button you've clicked
                var toggleClick = $(this);
                // this reads the rel attribute of the button to determine which div id to toggle
                var toggleDiv = $(this).attr('rel');
                // here we toggle show/hide the correct div at the right speed and using which easing effect
                $(toggleDiv).slideToggle(options.speed, options.easing, function () {
                    // this only fires once the animation is completed
                    if (options.changeText == 1) {
                        $(toggleDiv).is(":visible") ? toggleClick.html(options.hideText) : toggleClick.html(options.showText);
                    }
                });

                return false;

            });

        };

    })(jQuery);
</script>