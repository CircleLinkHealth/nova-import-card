@extends('cpm-admin::partials.adminUI')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1>Time Tracker Events</h1>
        </div>
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <time-tracker-events ws-root-url="{{ config('services.ws.root') }}"
                                         ref="timeTrackerEventsComponent"></time-tracker-events>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <style>
        .panel-body {
            font-size: 20px;
        }
    </style>
@endpush
@endsection