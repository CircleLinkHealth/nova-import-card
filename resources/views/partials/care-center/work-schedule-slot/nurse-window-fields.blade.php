<div class="form-group">

    <div class="col-md-4">
        <select class="form-control" name="date" required>
            @foreach(weekDays() as $key => $day)
                <option value="{{carbonToClhDayOfWeek($key)}}">{{$day}}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <input class="form-control" name="window_time_start"
               type="time" data-field="time"
               id="window_time_start"
               value="{{ old('window_time_start') ? old('window_time_start') : '09:00' }}"
               required>
    </div>

    <div class="col-md-3">
        <input class="form-control" name="window_time_end"
               type="time" data-field="time"
               id="window_time_end"
               value="{{ old('window_time_end') ?old('window_time_end') : '17:00' }}"
               required>
    </div>

    <div class="col-md-2">
        <input type="submit" class="btn btn-primary" value="{{ isset($submitBtnText) ? $submitBtnText : 'Save Hours' }}"
               name="submit" id="store-window">
    </div>
</div>