
<form method="GET" action="{{(route('admin.reports.nurse.weekly'))}}">
    <div class="form-group">
        <label for="day_time">Choose date:</label>
        <input type="date" class="form-control" id="date" name="date" value="{{$date->toDateString()}}"
               style="width: 250px"
               max="{{$yesterdayDate->toDateString()}}" required>
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</form>
