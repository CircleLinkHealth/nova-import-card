<style>
    .list-group-item {
        padding: 2px;
    }
</style>

<div class="row">
    <h3>Your Schedule</h3>
</div>

@if($windows->isEmpty())
    <div class="row">
        You do not have any Windows yet. Go ahead and create some!
    </div>
@else
    @foreach(weekDays() as $day)
        <div class="row">
            <dl class="dl-horizontal">
                <dt>{{ucfirst($day)}}</dt>
                <dd>
                    @foreach($windows as $window)
                        @if (strcasecmp(clhDayOfWeekToDayName($window->day_of_week), $day) == 0)
                            <div class="col-md-2 list-group-item text-center">
                                <b>
                                    {{ Carbon\Carbon::parse($window->window_time_start)->format('H:i') }}
                                </b> -
                                <b>
                                    {{ Carbon\Carbon::parse($window->window_time_end)->format('H:i') }}
                                </b>

                                &nbsp;

                                <a href="{{ route('care.center.work.schedule.destroy', $window->id) }}"
                                   onclick="return confirm('Are you sure you want to delete this slot?')"
                                   id="delete-window-{{$window->id}}">
                                    <i class="glyphicon glyphicon-trash"></i>
                                </a>
                            </div>
                        @endif
                    @endforeach
                </dd>
            </dl>
        </div>
    @endforeach
@endif
