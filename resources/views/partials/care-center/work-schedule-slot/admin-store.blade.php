{!! Form::open([
    'url' => route('care.center.work.schedule.store'),
    'method' => 'post'
]) !!}

<input type="hidden" value="{{$nurseInfo->id}}" name="nurse_info_id">

@include('partials.care-center.work-schedule-slot.nurse-window-fields', [ 'submitBtnText' => 'Add New Window', 'tzAbbr' => $nurseInfo->user->timezone_abbr ])

{!! Form::close() !!}