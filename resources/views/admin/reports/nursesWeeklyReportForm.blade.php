<form method="POST" action="{{route('admin.reports.nurse.filterDay')}}">
   {{-- {{csrf_field()}}--}}

    <div class="form-group">
        <label for="day_time">Date:</label>
        <input type="date" class="form-control" id="date" name="date"
               style="width: 250px"
               min="{{Carbon\Carbon::now()->toDateTimeString()}}" required>
    </div>

    <div class="form-group">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
</form>
