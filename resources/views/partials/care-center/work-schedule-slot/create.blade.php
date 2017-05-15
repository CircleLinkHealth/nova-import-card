<form action="{{ route('care.center.work.schedule.store') }}" method="post">
    {{ csrf_field() }}
    @include('partials.care-center.work-schedule-slot.nurse-window-fields')
</form>