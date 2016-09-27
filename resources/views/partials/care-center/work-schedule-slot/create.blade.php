<form action="{{ route('care.center.work.schedule.store') }}" method="post">
    <div class="form-group">

        <div class="col-md-4">
            {{--<label for="date">Date</label>--}}
            <input class="form-control" name="date"
                   type="text" data-field="date"
                   id="date"
                   placeholder="Date"
                   required>
        </div>

        <div class="col-md-3">
            {{--<label for="window_time_start">Start Time</label>--}}
            <input class="form-control" name="window_time_start"
                   type="text" data-field="time"
                   id="window_time_start"
                   placeholder="Window Time Start"
                   required>
        </div>

        <div class="col-md-3">
            {{--<label for="window_time_end">End Time</label>--}}
            <input class="form-control" name="window_time_end"
                   type="text" data-field="time"
                   id="window_time_end"
                   placeholder="Window Time End"
                   required>
        </div>

        <div class="col-md-2">
            <input type="submit" class="btn btn-primary" value="Store Window"
                   name="submit" id="store-window">
        </div>
    </div>
</form>


<div id="dtBox"></div>

<script>
    $(document).ready(function () {
        $("#dtBox").DateTimePicker({
            dateFormat: "MM-dd-yyyy",
            minuteInterval: 30
        });

        $('[data-toggle="tooltip"]').tooltip();
    });
</script>