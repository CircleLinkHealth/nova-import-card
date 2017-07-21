<style>
    .work-schedule-label {
        line-height: 4rem;
        font-weight: bold !important;
    }

    .minimum-padding {
        padding: 0 1px 0 1px;
    }
</style>

<div class="form-group">

    <div class="col-md-4">
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

    <div class="col-md-3">
        <div class="row">
            <div class="col-md-4 minimum-padding">
                <label class="work-schedule-label">from ({{$tzAbbr}})</label>
            </div>
            <div class="col-md-8 minimum-padding">
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
        </div>
    </div>

    <div class="col-md-3">
        <div class="col-md-4 minimum-padding">
            <label class="work-schedule-label">until ({{$tzAbbr}})</label>
        </div>
        <div class="col-md-8 minimum-padding">
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
    </div>

    <div class="col-md-2">
        <input type="submit" class="btn btn-primary" value="{{ isset($submitBtnText) ? $submitBtnText : 'Save Hours' }}"
               name="submit" id="store-window">
    </div>
</div>