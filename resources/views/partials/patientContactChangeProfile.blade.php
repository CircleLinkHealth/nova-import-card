<div class="col-md-12" style="">
    <div class="row">
        <label for="contact_day">Contact Days</label>
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
</div>

<div class="col-md-12" style="">
    <div class="row">
        <label for="window_start">Call Start Time</label>
        <input class="form-control" name="window_start" type="time"
               value="{{$patient->patientInfo->daily_contact_window_start}}"
               id="window_start" placeholder="time">
    </div>
</div>

<div class="col-md-12" style="">
    <div class="row">
        <label for="window_end">Call End Time</label>
        <input class="form-control" name="window_end" type="time"
               value="{{$patient->patientInfo->daily_contact_window_end}}"
               id="window_end" placeholder="time">
    </div>
</div>

<div class="col-md-12" style="">
    <div class="row">
        <label for="frequency">Frequency</label>
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