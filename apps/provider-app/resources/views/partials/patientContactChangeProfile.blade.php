<div class="col-md-12" style="">
    <div class="row" style="padding-bottom: 10px">
        <label for="contact_day">Contact Days</label>
        <select id="days" name="days[]"
                class="selectpicker dropdown Valid form-control"
                data-size="7" style="width: 155px"
                multiple>
            <option value="1" {{in_array("1", $contact_days_array ) ? "selected" : ''}}>Mon</option>
            <option value="2" {{in_array(" 2",$contact_days_array) ? "selected" : ''}}>Tue</option>
            <option value="3" {{in_array(" 3",$contact_days_array) ? "selected" : ''}}>Wed</option>
            <option value="4" {{in_array(" 4",$contact_days_array) ? "selected" : ''}}>Thu</option>
            <option value="5" {{in_array(" 5",$contact_days_array) ? "selected" : ''}}>Fri</option>
            <option value="6" {{in_array(" 6",$contact_days_array) ? "selected" : ''}}>Sat</option>
            <option value="7" {{in_array(" 7",$contact_days_array) ? "selected" : ''}}>Sun</option>
        </select>
    </div>
</div>

<div class="col-md-12" style="">
    <div class="row" style="padding-bottom: 10px">
        <label for="window_start">Calls Start Time</label>
        <input class="form-control" name="window_start" type="time"
               value="{{isset($contactWindows[0]) ? $contactWindows[0]->window_time_start : ''}}"
               id="window_start" placeholder="00:00 AM" pattern="\d{2}\:\d{2} (AM|PM)">
    </div>
</div>

<div class="col-md-12" style="">
    <div class="row" style="padding-bottom: 10px">
        <label for="window_end">Calls End Time</label>
        <input class="form-control" name="window_end" type="time"
               value="{{isset($contactWindows[0]) ? $contactWindows[0]->window_time_end : ''}}"
               id="window_end" placeholder="00:00 AM" pattern="\d{2}\:\d{2} (AM|PM)">
    </div>
</div>

<div class="col-md-12" style="">
    <div class="row" style="padding-bottom: 10px">
        <label for="frequency">Frequency</label>
        <select id="frequency" name="frequency"
                class="selectpickerX dropdown Valid form-control" data-size="2"
                {{--style="width: 110px"--}}>
            @if(!$patient->patientInfo)
                <option value="1"> 1x Monthly</option>
                <option value="2" selected> 2x Monthly</option>
                <option value="3"> 3x Monthly</option>
                <option value="4"> 4x Monthly</option>
            @else
                <option value="1" {{$patient->patientInfo->preferred_calls_per_month == 1 ? 'selected' : ''}}> 1x
                    Monthly
                </option>
                <option value="2" {{$patient->patientInfo->preferred_calls_per_month == 2 ? 'selected' : ''}}> 2x
                    Monthly
                </option>
                <option value="3" {{$patient->patientInfo->preferred_calls_per_month == 3 ? 'selected' : ''}}> 3x
                    Monthly
                </option>
                <option value="4" {{$patient->patientInfo->preferred_calls_per_month == 4 ? 'selected' : ''}}> 4x
                    Monthly
                </option>
            @endif
        </select>
    </div>
</div>