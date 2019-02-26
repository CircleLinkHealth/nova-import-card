@if($nurse)
    <div id="v-show-nurse-work-schedule" class="row-centered nurse-dashboard-schedule hidden-xs">
        <notifications class="text-left"></notifications>
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <h3>
                        Your Schedule ({{auth()->user()->timezone_abbr}})
                    </h3>
                    <a href="{{ route('care.center.work.schedule.index') }}" id="work-schedule-link"
                       class="edit-work-schedule btn btn-primary">
                        Create/Edit Schedule
                    </a>
                </div>
                @include('partials.care-center.work-schedule-slot.index', [
                           'windows' => $nurse->windows,
                           'holidaysThisWeek' => $nurse->holidays_this_week,
                           'nurse' => $nurse
                       ])
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <h3>
                        Your Days Off
                    </h3>
                </div>
                @include('partials.care-center.holiday-schedule.index', [
                            'holidays' => $nurse->upcoming_holiday_dates
                        ])
            </div>
        </div>
    </div>
@endif