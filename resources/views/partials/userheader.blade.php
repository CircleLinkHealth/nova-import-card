<div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">
    <div class="row">
        <div class="col-sm-12">
            <p class="text-medium clearfix">
                <span class="pull-left"><strong>
                        <?php
                        $provider = App\User::find($patient->getBillingProviderIDAttribute());
                        ?>
                        @if($provider)
                            Provider: </strong> {{$provider->getFullNameAttribute()}} <strong> 
                        @else
                            Provider: <em>No Provider Selected </em>
                        @endif
                        Location:</strong>
                                <?= (empty($patient->getPreferredLocationName())) ?  'Not Set' : $patient->getPreferredLocationName();  ?>
                </span>
                <?php
                    // calculate display, fix bug where gmdate('i:s') doesnt work for > 24hrs
                $seconds = $patient->patientInfo()->first()->cur_month_activity_time;
                $H = floor($seconds / 3600);
                $i = ($seconds / 60) % 60;
                $s = $seconds % 60;
                $monthlyTime = sprintf("%02d:%02d:%02d",$H, $i, $s);
                ?>
               <a href="{{URL::route('patient.activity.providerUIIndex', array('patient' => $patient->ID))}}"><span class="pull-right">{{
                date("F", mktime(0, 0, 0, Carbon\Carbon::now()->month, 10))
                 }} Time: {{ $monthlyTime }}</span></a></p>
            <a href="{{ URL::route('patient.summary', array('patient' => $patient->ID)) }}">
                <span class="person-name text-big text-dark text-serif" title="{{$patient->ID}}">{{$patient->fullName}}</span></a>
            <ul class="person-info-list inline-block text-medium">
                <li class="inline-block">DOB: {{$patient->birthDate}}</li>
                <li class="inline-block">{{$patient->gender}}</li>
                <li class="inline-block">{{$patient->age}} yrs</li>
                <li class="inline-block">{{$patient->phone}}</li>
                <li class="inline-block">
                    <select id="status" name="status" class="selectpickerX dropdownValid form-control" data-size="2" style="width: 100px">
                            <option value="" {{$patient->ccm_status == 'enrolled' ? 'selected' : ''}}> Enrolled</option>
                            <option value="" {{$patient->ccm_status == 'withdrawn' ? 'selected' : ''}}> Withdrawn</option>
                            <option value="" {{$patient->ccm_status == 'paused' ? 'selected' : ''}}> Paused</option>
                    </select>
                </li>

                @if(Route::current()->getName() == 'patient.note.create')
                <li class="inline-block">Patient Contact Times: {{$patient->patientInfo->preferred_contact_time}} on {{
                    \App\PatientInfo::numberToTextDaySwitcher($patient->patientInfo->preferred_cc_contact_days)
                }} </li>

                <a class="show_hide" href="#" rel="#slidingDiv">

                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>

                </a></pre>
                <div class="form-block col-md-12">
                    <div id="slidingDiv" class="" style="display: none; border:0px; padding: 10px 35px;">
                        <div class="row" >
                            <label class="col-xs-4" style="padding-left: 0px;" for="contact_days">Contact Days</label>
                            <label class="col-xs-4" style="padding-left: 0px; left: -4px" for="windows[]">Contact Time</label>
                            <label class="col-xs-4" style="left: -20px" for="frequency">Call Frequency</label>
                        </div>

                        <div class="col-xs-4" style="padding-left: 0px;">
                            <select id=contact_days" name=days[]"
                                    class="selectpicker dropdown Valid form-control"
                                    data-size="7" style="width: 155px"
                                    multiple>
                                <option value=""  {{in_array("1", explode(',',$patient->patientInfo->preferred_cc_contact_days)) ? "selected" : ''}}> Mon </option>
                                <option value="" {{in_array(" 2",explode(',',$patient->patientInfo->preferred_cc_contact_days)) ? "selected" : ''}}> Tue </option>
                                <option value="" {{in_array(" 3",explode(',',$patient->patientInfo->preferred_cc_contact_days)) ? "selected" : ''}}> Wed </option>
                                <option value="" {{in_array(" 4",explode(',',$patient->patientInfo->preferred_cc_contact_days)) ? "selected" : ''}}> Thu </option>
                                <option value="" {{in_array(" 5",explode(',',$patient->patientInfo->preferred_cc_contact_days)) ? "selected" : ''}}> Fri </option>
                                <option value="" {{in_array(" 6",explode(',',$patient->patientInfo->preferred_cc_contact_days)) ? "selected" : ''}}> Sat </option>
                                <option value="" {{in_array(" 7",explode(',',$patient->patientInfo->preferred_cc_contact_days)) ? "selected" : ''}}> Sun </option>
                            </select>
                        </div>
                        <div class="col-xs-4" style="padding-left: 0px;">
                            <select id="contact_time" name="windows[]" class="selectpicker dropdown Valid form-control" data-size="3" style="width: 110px">
                                <option value="9:30am - 12n" {{$window == App\PatientInfo::CALL_WINDOW_0930_1200 ? 'selected' : ''}}>9:30-12</option>
                                <option value="12n - 3pm" {{$window == App\PatientInfo::CALL_WINDOW_1200_1500 ? 'selected' : ''}}>12-3</option>
                                <option value="3pm - 6pm" {{$window == App\PatientInfo::CALL_WINDOW_1500_1800 ? 'selected' : ''}}>3-6</option>
                            </select>
                        </div>
                        <div class="col-xs-4" style="padding-left: 0px;">
                            <select id="contact_time" name="contact_time" class="selectpickerX dropdown Valid form-control" data-size="2" style="width: 150px">
                                <option value=""> 1x Monthly</option>
                                <option value=""> 2x Monthly</option>
                                <option value=""> 3x Monthly</option>
                                <option value=""> 4x Monthly</option>
                            </select>
                        </div>
                    </div>

        @endif
            </ul>
        </div>
    </div>
    @if($patient->agentName)
    <div class="row">
        <div class="col-sm-12">
            <ul class="person-info-listX inline-block text-medium">
                <li class="inline-block">Alternate Contact: <span title="{{$patient->agentEmail}}">({{$patient->agentRelationship}}) {{$patient->agentName}}&nbsp;&nbsp;</span></li>
                <li class="inline-block">{{$patient->agentPhone}}</li>
            {{--</ul><div style="clear:both"></div><ul class="person-conditions-list inline-block text-medium"></ul>--}}
        </div>
    </div>
    @endif

    @if(!empty($problems))
        <div style="clear:both"></div>
        <ul class="person-conditions-list inline-block text-medium">
            @foreach($problems as $problem)
                <li class="inline-block"><input type="checkbox" id="item27" name="condition27" value="Active"
                                                checked="checked" disabled="disabled">
                    <label for="condition27"><span> </span>{{$problem}}</label>
                </li>
            @endforeach
        </ul>
    @endif
</div>

<script>
    $(document).ready(function(){

        var showText =  "<span class=\"glyphicon glyphicon-pencil\" aria-hidden=\"true\"></span>";

        $('.show_hide').showHide({
            speed: 1000, // speed you want the toggle to happen
            easing: '', // the animation effect you want. Remove this line if you dont want an effect and if you haven't included jQuery UI
            changeText: 1, // if you dont want the button text to change, set this to 0
            showText: 'Edit',// the button text to show when a div is closed
            hideText: 'Save' // the button text to show when a div is open

        });

    });

    (function ($) {
        $.fn.showHide = function (options) {

            //default vars for the plugin
            var defaults = {
                speed: 1500,
                easing: '',
                changeText: 0,
                showText: 'Save',
                hideText: 'Edit'

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
                $(toggleDiv).slideToggle(options.speed, options.easing, function() {
// this only fires once the animation is completed
                    if(options.changeText==1){
                        $(toggleDiv).is(":visible") ? toggleClick.text(options.hideText) : toggleClick.text(options.showText);
                    }
                });

                return false;

            });

        };
    })(jQuery);


</script>


