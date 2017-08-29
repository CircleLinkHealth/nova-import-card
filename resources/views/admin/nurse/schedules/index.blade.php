@extends('partials.adminUI')

@section('content')
    <div id="app">
        @include('errors.errors')

        <div class="container">
            @foreach($data as $user)
                <div class="row" style="padding-bottom: 10%;">
                    <h3>
                        <b>{{ $user->fullName }}</b>
                        <span class="pull-right red-text">Timezone: {{ $user->timezone ? $user->timezone : 'Not set' }}</span>
                    </h3>

                    <div class="row">
                        @include('partials.care-center.work-schedule-slot.admin-store', [ 'nurseInfo' => $user->nurseInfo ])
                    </div>

                    @if($user->nurseInfo->windows->count() > 0)
                        <h4>Existing Windows</h4>
                    @else
                        <h4>This nurse does not have any windows.</h4>
                    @endif

                    <div class="row">
                        <div class="row">
                            <h3>{{$user->fullName}}'s Schedule</h3>
                        </div>
                        <div class="col-md-12">
                            @include('partials.care-center.work-schedule-slot.index', [
                                'windows' => $user->nurseInfo->windows,
                                'holidaysThisWeek' => $user->nurseInfo->holidays_this_week,
                                'nurse' => $user->nurseInfo
                            ])
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <h3>{{$user->fullName}}'s Days Off</h3>
                            </div>
                            @include('partials.care-center.holiday-schedule.index', [
                                'holidays' => $user->nurseInfo->upcoming_holiday_dates
                            ])
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script src="{{asset('compiled/js/nurse-work-schedule.js')}}"></script>

@endsection