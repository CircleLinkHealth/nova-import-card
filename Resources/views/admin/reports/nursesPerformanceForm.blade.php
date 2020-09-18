<form method="GET" action="{{(route('admin.reports.nurse.metrics'))}}">
    <div class="form-group" style="float: left; margin-right: 1%;">
        <label for="day_time">Data from:</label>
        <input type="date" class="form-control" id="start_date" name="start_date" value="{{$startDate->toDateString()}}"
               style="width: 250px; display:inline-block;"
               min="{{$limitDate->toDateString()}}"
               max="{{$yesterdayDate->toDateString()}}" required>
    </div>

    <div class="form-group" style="float: left; margin-right: 1%;">
        <label for="day_time">To:</label>
        <input type="date" class="form-control" id="end_date" name="end_date" value="{{$endDate->toDateString()}}"
               style="width: 250px; display:inline-block;"
               min="{{$limitDate->toDateString()}}"
               max="{{$yesterdayDate->toDateString()}}" required>

        <button type="submit" class="btn btn-primary">Submit</button>
    </div>

    <div class="form-group" style="float: right; margin-right: 1%;">
        <span class="form-control">
            <a class="excel-export" data-href="{{ route('admin.reports.nurse.performance.excel') }}">Export Excel</a>
        </span>
    </div>
</form>
