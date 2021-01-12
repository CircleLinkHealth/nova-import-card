<div class="form-group text-left">
    <div class="col-md-12">
        <div class="minimum-padding">
            <select class="form-control" name="day_of_week" required>
                @foreach(weekDays() as $key => $day)
                    @if(!isset($window))
                        <option value="{{$key}}" {{old('day_of_week') == $key ? 'selected' : '' }}>{{$day}}</option>
                    @else
                        <option value="{{$key}}" {{$window->day_of_week == $key ? 'selected' : '' }}>{{$day}}</option>
                    @endif
                @endforeach
            </select>
        </div>

        <div class="minimum-padding">
            <label class="work-schedule-label">for</label>
        </div>

        <div class="minimum-padding">
            <input class="form-control" name="work_hours"
                   type="number" data-field="time" min="1" max="12"
                   id="work_hours" style="max-width: 60px;"
                   value="{{ old('work_hours') ? old('work_hours') :  '5' }}"
                   required>
        </div>

        <div class="minimum-padding">
            <label class="work-schedule-label">work hours between ({{$tzAbbr}})</label>
        </div>
        <div class="minimum-padding">
            <input class="form-control" name="window_time_start"
                   type="time" data-field="time"
                   id="window_time_start"
                   @if (isset($window))
                   value="{{$window->window_time_start}}"
                   @else
                   value="{{ old('window_time_start') ? old('window_time_start') :  '09:00' }}"
                   @endif
                   required
                   style="max-width: 120px;">
        </div>

        <div class="minimum-padding">
            <label class="work-schedule-label">and ({{$tzAbbr}})</label>
        </div>
        <div class="minimum-padding">
            <input class="form-control" name="window_time_end"
                   type="time" data-field="time"
                   id="window_time_end"
                   @if (isset($window))
                   value="{{$window->window_time_end}}"
                   @else
                   value="{{ old('window_time_end') ? old('window_time_end') :  '17:00' }}"
                   @endif
                   required
                   style="max-width: 120px;">
        </div>

        <div class="minimum-padding">
            <label class="info tooltip-bottom"
                   data-tooltip="Having a separate input for total hours and time range allows you to work on and off during a time range. E.g., you can input 4 hours but work those 4 hours at some point between 10am and 5pm, depending on when patients have requested to be called.">
                <span class="glyphicon glyphicon-question-sign" aria-hidden="true" style="font-size: 20px;"></span>
            </label>
        </div>

        <div class="minimum-padding" style="margin-left: 5%;">
            <input type="submit" class="btn btn-primary"
                   value="{{ isset($submitBtnText) ? $submitBtnText : 'Save Hours' }}"
                   name="submit" id="store-window">
        </div>
    </div>
</div>