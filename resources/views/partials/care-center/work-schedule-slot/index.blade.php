@if($windows->isEmpty())
    <div class="row">
        You do not have any Windows yet. Go ahead and create some!
    </div>
@else
    <div class="row">
        <table class="table" style="width: 100%;">
            <tr>
                @foreach(weekDays() as $index => $day)
                    @if (Route::currentRouteName() == 'get.admin.nurse.schedules')

                        <th style="width: 14.29%;" class="text-center">
                            {{ucfirst($day)}}

                            <br>

                            @if($nurse->workhourables->first() && $nurse->workhourables->first()->{strtolower($day)})
                                {{ $nurse->workhourables->first()->{strtolower($day)} }} hrs
                            @endif

                        </th>

                    @else

                        <th style="width: 14.29%;" class="text-center">{{ucfirst($day)}}
                            <nurse-daily-hours day="{{strtolower($day)}}"
                                               hours="@if($nurse->workhourables->first()) {{$nurse->workhourables->first()->toJson()}} @endif"
                                               windows="{{ $windows->where('day_of_week', $index)->values()->toJson() }}"
                            >
                            </nurse-daily-hours>
                        </th>

                    @endif
                @endforeach
            </tr>

            <tr>

                @foreach(weekDays() as $index => $day)
                    <td>
                        @if (in_array($day, $holidaysThisWeek))
                            <div class="list-group-item text-center" style="padding: 2px;">
                                <b>
                                    HOLIDAY!
                                </b>
                            </div>
                        @else
                            @foreach($windows as $window)
                                @if (strcasecmp(clhDayOfWeekToDayName($window->day_of_week), $day) == 0)
                                    <div class="list-group-item text-center" style="padding: 2px;">
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
                    </td>
                @endforeach
            </tr>

        </table>
    </div>
@endif
