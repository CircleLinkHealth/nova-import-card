@extends('partials.providerUI')

@section('title', 'Patient Note Creation')
@section('activity', 'Patient Note Creation')

@section('content')
    <?php
    $userTime = \Carbon\Carbon::now();
    $userTime->setTimezone($userTimeZone);
    $userTimeGMT = \Carbon\Carbon::now()->setTimezone('GMT');
    $userTime = $userTime->format('Y-m-d\TH:i');
    $userTimeGMT = $userTimeGMT->format('Y-m-d\TH:i');
    ?>

    <style>
        .edit_button {
            -webkit-appearance: none;
            outline: none;
            border: 0;
            background: transparent;
        }
    </style>

    <div class="row" style="margin-top:60px;">
        <div class="main-form-container col-lg-6 col-lg-offset-3 col-md-10 col-md-offset-1"
             style="border-bottom: 3px solid #50b2e2;">
            <div class="row">
                <div class="main-form-title col-lg-12">
                    Record New Note
                </div>
                {!! Form::open(array('url' => URL::route('patient.note.store', ['patientId' => $patient]), 'class' => 'form-horizontal')) !!}

                @include('partials.userheader')


                <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12"
                     style=" border-bottom:3px solid #50b2e2;padding: 10px 48px;">

                    <div class="col-xs-12" style="">
                        <input type="text" class="form-control" name="general_comment" id="general_comment"
                               value="{{$patient->patientInfo->general_comment}}"
                               placeholder="{{$patient->patientInfo->general_comment == '' ? 'Enter General Comment...' : $patient->patientInfo->general_comment}}"
                               aria-describedby="sizing-addon2" style="margin: 0 auto; text-align: left; color: #333;">
                    </div>

                    <!-- The next div is the contact statement -->

                    <div class="col-xs-12 inline-block row" style=" padding: 0px; width: 98%; text-align: center;">
                        Call Times: <span id="start_window_text">{{Carbon\Carbon::parse($patient->patientInfo->daily_contact_window_start)->format('H:i')}}</span> to <span id="end_window_text">{{Carbon\Carbon::parse($patient->patientInfo->daily_contact_window_end)->format('H:i')}}</span>
                        on <span id="days_text">{{\App\PatientInfo::numberToTextDaySwitcher($patient->patientInfo->preferred_cc_contact_days)}}</span>; <span id="frequency_text">{{$patient->patientInfo->preferred_calls_per_month}}</span>x Monthly


                        <button type="" class="show_hide edit_button" href="#" rel="#slidingDiv" onclick="change_contact_string()">
                            <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                        </button>


                        <div class="form-block col-md-12">
                            <div id="slidingDiv" class="" style="display: none; margin: 0 auto;
    text-align: left;border:0px; padding: 10px 35px;">
                                <div class="row">
                                    <label class="col-xs-4" style="padding-left: -1px;" for="contact_day">Contact Days</label>
                                    <label class="col-xs-3" style="padding-left: 0px; left: 0px" for="window_start">Call Start Time</label>
                                    <label class="col-xs-3" style="padding-left: 0px; left: 0px" for="window_end">Call End Time</label>
                                    <label class="col-xs-1" style="left: -20px" for="frequency">Frequency</label>
                                </div>

                                <div class="col-xs-4" style="padding-left: 0px;">
                                    <select id=contact_days" name=days[]"
                                            class="selectpicker dropdown Valid form-control"
                                            data-size="7" style="width: 155px"
                                            multiple>
                                        <option value="1" {{in_array("1", explode(',',$patient->patientInfo->preferred_cc_contact_days)) ? "selected" : ''}}>
                                            Mon
                                        </option>
                                        <option value="2" {{in_array(" 2",explode(',',$patient->patientInfo->preferred_cc_contact_days)) ? "selected" : ''}}>
                                            Tue
                                        </option>
                                        <option value="3" {{in_array(" 3",explode(',',$patient->patientInfo->preferred_cc_contact_days)) ? "selected" : ''}}>
                                            Wed
                                        </option>
                                        <option value="4" {{in_array(" 4",explode(',',$patient->patientInfo->preferred_cc_contact_days)) ? "selected" : ''}}>
                                            Thu
                                        </option>
                                        <option value="5" {{in_array(" 5",explode(',',$patient->patientInfo->preferred_cc_contact_days)) ? "selected" : ''}}>
                                            Fri
                                        </option>
                                        <option value="6" {{in_array(" 6",explode(',',$patient->patientInfo->preferred_cc_contact_days)) ? "selected" : ''}}>
                                            Sat
                                        </option>
                                        <option value="7" {{in_array(" 7",explode(',',$patient->patientInfo->preferred_cc_contact_days)) ? "selected" : ''}}>
                                            Sun
                                        </option>
                                    </select>
                                </div>

                                <div class="col-xs-3" style="padding-left: 0px;">
                                    <input class="form-control" name="window_start" type="time"
                                           value="{{$patient->patientInfo->daily_contact_window_start}}"
                                           id="window_start" placeholder="time">
                                </div>

                                <div class="col-xs-3" style="padding-left: 0px;">
                                    <input class="form-control" name="window_end" type="time"
                                           value="{{$patient->patientInfo->daily_contact_window_end}}"
                                           id="window_end" placeholder="time">
                                </div>
                                <div class="col-xs-2" style="padding-left: 0px;">
                                    <select id="frequency" name="frequency"
                                            class="selectpickerX dropdown Valid form-control" data-size="2"
                                            style="width: 150px" >
                                        <option value="1" {{$patient->patientInfo->preferred_calls_per_month == 1 ? 'selected' : ''}}> 1x Monthly</option>
                                        <option value="2" {{$patient->patientInfo->preferred_calls_per_month == 2 ? 'selected' : ''}}> 2x Monthly</option>
                                        <option value="3" {{$patient->patientInfo->preferred_calls_per_month == 3 ? 'selected' : ''}}> 3x Monthly</option>
                                        <option value="4" {{$patient->patientInfo->preferred_calls_per_month == 4 ? 'selected' : ''}}> 4x Monthly</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12"
                     style=" border:0px solid #50b2e2;padding: 10px 35px;">
                    <div class="form-block col-md-6">
                        <div class="row">
                            <div class="new-note-item">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label for="activityKey">
                                            Note Topic
                                        </label>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <select id="activityKey" name="type"
                                                    class="selectpickerX dropdownValid form-control"
                                                    data-size="10" required>
                                                <option value=""> Select Topic</option>
                                                @foreach ($note_types as $note_type)
                                                    <option value="{{$note_type}}"> {{$note_type}} </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-block col-md-6">
                        <div class="row">
                            <div class="new-note-item">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label for="observationDate">
                                            When (Patient Local Time):
                                        </label>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <input name="performed_at" type="datetime-local"
                                                   class="selectpickerX form-control"
                                                   data-width="95px" data-size="10" list max="{{$userTime}}"
                                                   value="{{$userTime}}"
                                                   required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-block col-md-6">
                        <div class="row">
                            <div class="new-note-item">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label for="activityKey">
                                            Performed By
                                        </label>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <select id="author_id" name="author_id"
                                                    class="selectpickerX dropdown Valid form-control" data-size="10"
                                                    required disabled>
                                                <option value="{{$author_id}}" selected> {{$author_name}} </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-block col-md-6">
                        <div class="row">
                            <div class="new-note-item">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <div class="panel-group" id="accordion">
                                            <label data-toggle="collapse" data-target="#collapseOne">
                                                <div class="radio"><input type="checkbox" name="meta[0][meta_key]"
                                                                          id="phone"
                                                                          value="phone"/><label
                                                            for="phone"><span> </span>Patient Phone Session</label>
                                                </div>
                                            </label>

                                            <div id="collapseOne" class="panel-collapse collapse in">
                                                <div class="radio-inline"><input type="radio"
                                                                                 name="phone"
                                                                                 value="inbound" id="Inbound"/><label
                                                            for="Inbound"><span> </span>Inbound</label>
                                                </div>
                                                <div class="radio-inline"><input type="radio"
                                                                                 name="phone"
                                                                                 value="outbound" id="Outbound"/><label
                                                            for="Outbound"><span> </span>Outbound</label></div>
                                                <input type="hidden" name="call_status" value="">
                                                <div>
                                                    <div class="radio-inline"><input type="checkbox"
                                                                                     name="call_status"
                                                                                     value="reached"
                                                                                     id="reached"/><label
                                                                for="reached"><span> </span>Successful Clinical
                                                            Call</label>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="tcm" value="hospital">
                                                <div>
                                                    <div class="radio-inline"><input type="checkbox"
                                                                                     name="tcm"
                                                                                     value="true" id="true"/><label
                                                                for="true"><span> </span>Patient in Hospital/ER (now or
                                                            recently)</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-block col-md-12">
                        <div class="row">
                            <div class="new-note-item">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <input type="hidden" name="body" value="body">
                                        <textarea id="note" class="form-control" rows="10" cols="100"
                                                  placeholder="Enter Note..."
                                                  name="body" required></textarea> <br/>
                                    </div>
                                </div>
                                <div class="form-block col-md-6">
                                    <div class="row">
                                        <div class="new-note-item">
                                            <div class="form-group">
                                                <div class="col-sm-12">
                                                    <label for="activityKey">
                                                        Send Note To:
                                                    </label>
                                                </div>
                                                <div class="col-sm-12">
                                                    <div class="form-group">
                                                        <select id=performedBy" name=careteam[]"
                                                                class="selectpicker dropdown Valid form-control"
                                                                data-size="10"
                                                                multiple>
                                                            @foreach ($careteam_info as $id => $name)
                                                                <option value="{{$id}}"> {{$name}} </option>
                                                            @endforeach
                                                            <option value="948">
                                                                Patient Support
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-sm-4">
                                    <input type="hidden" name="patient_id" value="{{$patient->ID}}">
                                    <input type="hidden" name="logger_id" value="{{Auth::user()->ID}}">
                                    <input type="hidden" name="author_id" value="{{Auth::user()->ID}}">
                                    <input type="hidden" name="patientID" id="patientID" value="{{$patient->ID}}">
                                    <input type="hidden" name="programId" id="programId" value="{{$program_id}}">
                                </div>
                                <div class="form-item form-item-spacing text-center">
                                    <div>
                                        <div class="col-sm-12">
                                            <input type="hidden" value="new_activity"/>
                                            <button id="update" name="submitAction" type="submit"
                                                    value="new_activity"
                                                    class="btn btn-primary btn-lg form-item--button form-item-spacing">
                                                Save/Send Note
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {!! Form::close() !!}


                                <script>

                                    $(document).ready(function() {

                                        console.log();

//                                        $("#contact_days").change(function() {
//
//                                            var tags = [];
//                                            $('#contact_days').each(function() {
//                                                tags.push($(this).val());
//                                            });
//
//                                            alert($("#contact_day"));
//
//                                            $('#days_text').html(tags.join(', '));
//                                        }).change();
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


                                    // Script is for the "phone session" part

                                    $('.collapse').collapse();

                                    $("input:checkbox").on('click', function () {
                                        // in the handler, 'this' refers to the box clicked on
                                        var $box = $(this);
                                        if ($box.is(":checked")) {
                                            // the name of the box is retrieved using the .attr() method
                                            // as it is assumed and expected to be immutable
                                            var group = "input:checkbox[name='" + $box.attr("name") + "']";
                                            // the checked state of the group/box on the other hand will change
                                            // and the current value is retrieved using .prop() method
                                            $(group).prop("checked", false);
                                            $box.prop("checked", true);
                                        } else {
                                            $box.prop("checked", false);
                                        }
                                    });

                                    // Script for editing the patient times

                                    $(document).ready(function () {


                                        $('.show_hide').showHide({
                                            speed: 1000, // speed you want the toggle to happen
                                            easing: '', // the animation effect you want. Remove this line if you dont want an effect and if you haven't included jQuery UI
                                            changeText: 1, // if you dont want the button text to change, set this to 0
                                            showText: "<span class=\"glyphicon glyphicon-pencil\" aria-hidden=\"true\"></span>", //the button text to show when a div is closed
                                            hideText: "<div style='color: #47beab;'> Hide </div>" // the button text to show when a div is open

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


                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    </div>
@endsection