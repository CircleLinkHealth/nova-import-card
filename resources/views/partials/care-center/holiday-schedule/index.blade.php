{{--@if($holidays->isEmpty())--}}
{{--    <div class="row">--}}
{{--        You do not have any days off.--}}
{{--    </div>--}}
{{--@else--}}
{{--    <div class="row">--}}
{{--        <ul class="list-group col-md-5">--}}
{{--            @foreach($holidays as $holiday)--}}

{{--                <li class="list-group-item" style="padding: 20px;">--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-md-11">--}}
{{--                            {{ clhDayOfWeekToDayName(carbonToClhDayOfWeek($holiday->date->dayOfWeek)) }},--}}
{{--                            <b>--}}
{{--                                {{ $holiday->date->format('M. d Y') }}--}}
{{--                            </b>--}}
{{--                        </div>--}}

{{--                        <!-- if it does not have an id, it is a company holiday -->--}}
{{--                        @if ($holiday->id)--}}
{{--                            <div class="col-md-1">--}}
{{--                                <a href="{{ route('care.center.work.schedule.holiday.destroy', $holiday->id) }}"--}}
{{--                                   onclick="return confirm('Are you sure you want to delete this slot?')"--}}
{{--                                   id="delete-window-{{$holiday->id}}">--}}
{{--                                    <i class="glyphicon glyphicon-trash"></i>--}}
{{--                                </a>--}}
{{--                            </div>--}}
{{--                        @endif--}}

{{--                    </div>--}}
{{--                </li>--}}

{{--            @endforeach--}}
{{--        </ul>--}}
{{--    </div>--}}
{{--@endif--}}
