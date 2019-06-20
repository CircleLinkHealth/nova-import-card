<form method="GET" action="{{(route('admin.reports.nurse.metrics'))}}">
    <div class="form-group" style="float: left; margin-right: 1%;">
        <label for="day_time">Choose start date:</label>
        <input type="date" class="form-control" id="start_date" name="start_date" value="{{$startDate->toDateString()}}"
               style="width: 250px"
               min="{{$limitDate->toDateString()}}"
               max="{{$yesterdayDate->toDateString()}}" required>
    </div>

    <div class="form-group">
        <label for="day_time">Choose end date:</label>
        <input type="date" class="form-control" id="end_date" name="end_date" value="{{$endDate->toDateString()}}"
               style="width: 250px"
               min="{{$limitDate->toDateString()}}"
               max="{{$yesterdayDate->toDateString()}}" required>
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</form>
