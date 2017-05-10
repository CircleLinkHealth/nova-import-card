{!! Form::open([
    'url' => route('patch.admin.edit.nurse.schedules', ['id' => $window->id]),
    'method' => 'patch'
]) !!}

@include('partials.care-center.work-schedule-slot.nurse-window-fields', [
    'submitBtnText' => 'Edit Window',
    'window'        => $window,
])

{!! Form::close() !!}
