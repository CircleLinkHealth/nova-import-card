@extends('partials.adminUI')
@section('content')
    <form method="GET" action="{{(route('admin.reports.nurse.weekly'))}}">
        <div class="form-group">
            <label for="day_time">Date</label>
            <input type="date" class="form-control" id="date" name="date"
                   style="width: 250px"
                   min="{{Carbon\Carbon::now()->toDateTimeString()}}" required>

            <div class="form-group">
               {{-- <input type="hidden" name="lesson_id" value="{{$lesson->id}}">
                <input type="hidden" name="level" value="{{$level->id}}">--}}
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </div>
    </form>
    @endSection