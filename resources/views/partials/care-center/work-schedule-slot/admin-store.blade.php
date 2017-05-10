{!! Form::open([
    'url' => route('post.admin.store.nurse.schedules', ['id' => $nurseInfo->id]),
    'method' => 'post'
]) !!}

@include('partials.care-center.work-schedule-slot.nurse-window-fields', [ 'submitBtnText' => 'Add New Window'])

{!! Form::close() !!}