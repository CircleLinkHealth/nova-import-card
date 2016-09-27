<h3>Your Schedule</h3>

<div class="row">
    <ul class="list-group col-md-5">
        @foreach($windows as $window)
            <li class="list-group-item">
                <div class="row">
                    <div class="col-md-11">
                        {{ clhDayOfWeekToDayName($window->day_of_week) }},
                        <b>
                            {{ Carbon\Carbon::parse($window->date)->format('M. d Y') }}
                        </b>, from
                        <b>
                            {{ $window->window_time_start }}
                        </b> to
                        <b>
                            {{ $window->window_time_end }}
                        </b>
                    </div>

                    <div class="col-md-1">
                        @if($window->deletable)
                            <a href="{{ route('care.center.work.schedule.destroy', $window->id) }}"
                               onclick="return confirm('Are you sure you want to delete this slot?')">
                                <i class="glyphicon glyphicon-trash"></i>
                            </a>
                        @else
                            <a href="#"
                               data-placement="right"
                               data-toggle="tooltip"
                               title="You cannot delete windows after Wednesday night of the week before.">
                                <i class="glyphicon glyphicon-info-sign"></i>
                            </a>
                        @endif

                    </div>
                </div>
            </li>
        @endforeach
    </ul>
</div>