@if($windows->isEmpty())
    <div class="row">
        You do not have any Windows yet. Go ahead and create some!
    </div>
@else
    @foreach(weekDays() as $day)
        <div class="row">
            <dl class="dl-horizontal">
                <dt>
                <div class="col-md-6 text-left">{{ucfirst($day)}}</div>
                <div class="col-md-6 text-right"
                     style="color: #5bc0de;">@if($nurse->workhourables->first() && $nurse->workhourables->first()->{strtolower($day)}) {{$nurse->workhourables->first()->{strtolower($day)} }}
                    hrs @endif</div>
                </dt>
                <dd>
                    @if (in_array($day, $holidaysThisWeek))
                        <div class="col-md-12 list-group-item text-center" style="padding: 2px;">
                            <b>
                                HOLIDAY!
                            </b>
                        </div>
                    @else
                        @foreach($windows as $window)
                            @if (strcasecmp(clhDayOfWeekToDayName($window->day_of_week), $day) == 0)
                                <div class="col-md-2 list-group-item text-center" style="padding: 2px;">
                                    <b>
                                        {{ Carbon\Carbon::parse($window->window_time_start)->format('h:i a') }}
                                    </b> -
                                    <b>
                                        {{ Carbon\Carbon::parse($window->window_time_end)->format('h:i a') }}
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
                    @endif
                </dd>
            </dl>
        </div>
    @endforeach
@endif
