<div class="form-group text-left">
    <div class="col-md-12">

        <div class="minimum-padding">
            <select class="form-control" name="day_of_week" required>
                @foreach(weekDays() as $key => $day)
                    @if(!isset($window))
                        <option value="{{carbonToClhDayOfWeek($key)}}">{{$day}}</option>
                    @else
                        <option value="{{carbonToClhDayOfWeek($key)}}" {{$window->day_of_week == carbonToClhDayOfWeek($key) ? 'selected' : '' }}>{{$day}}</option>
                    @endif
                @endforeach
            </select>
        </div>

        <div class="minimum-padding">
            <label class="work-schedule-label">for</label>
        </div>

        <div class="minimum-padding">
            <input class="form-control" name="work_hours"
                   type="number" data-field="time" min="1"
                   id="work_hours" style="max-width: 60px;"
                   value="5"
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
                   required>
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
                   required>
        </div>

        <div class="minimum-padding" style="margin-left: 5%;">
            <input type="submit" class="btn btn-primary"
                   value="{{ isset($submitBtnText) ? $submitBtnText : 'Save Hours' }}"
                   name="submit" id="store-window">
        </div>
    </div>
</div>