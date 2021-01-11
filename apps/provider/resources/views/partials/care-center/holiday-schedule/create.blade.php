<div class="form-group text-left">
    <div class="col-md-12 min minimum-padding">
        <form action="{{ route('care.center.work.schedule.holiday.store') }}" method="post">
            {{ csrf_field() }}
            <div class="form-group">

                <div class="col-md-4">
                    <input class="form-control" name="holiday"
                           type="date"
                           id="holiday"
                           placeholder="Date"
                           value="{{ isset($holiday) ? $holiday->date->format('m-d-Y') : old('holiday') }}"
                           required>
                </div>

                <div class="col-md-2">
                    <input type="submit" class="btn btn-info" value="Save Holiday"
                           name="submit" id="store-window">
                </div>
            </div>
        </form>
    </div>
</div>