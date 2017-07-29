@extends('partials.adminUI')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <form class="text-center" method="get"
                                  action="{{URL::route('admin.reports.nurse.allocation')}}">
                                <button type="submit"
                                        value="{{Carbon\Carbon::parse($month)->firstOfMonth()->subMonth(1)->toDateString()}}"
                                        style="display:inline-block;" name="previous"> <
                                </button>
                                <h1 class="title"
                                    style="display:inline-block;"> {{$month->format('F, Y')}} </h1>
                                <button value="{{Carbon\Carbon::parse($month)->firstOfMonth()->addMonths(1)->toDateString()}}"
                                        style="display:inline-block;" type="submit" name="next"> >
                                </button>
                            </form>
                            <hr>
                            <?php $count = 0; ?>
                            @foreach($data as $date => $nurses)
                                <?php

                                if ($count == 0) {

                                    $boxSpaces = Carbon\Carbon::parse($date)->dayOfWeek;

                                    for ($i = 0; $i < $boxSpaces; $i++) {

                                        $d = Carbon\Carbon::parse($date)->subDays($boxSpaces - $i)->format('m/d/y D');

                                        echo '<div class="container"style="display:inline-block; width: 130px; vertical-align: text-top;">
                                                  <h4 style="color: dimgray; font-size: 17px;">' . $d . '</h4>
                                             </div>';
                                    }
                                }

                                ?>

                                <div class="container"
                                     style="display:inline-block; width: 130px;  vertical-align: text-top;">
                                    <h4 style="font-size: 17px;"><b>{{$date}}</b></h4>
                                    <div class="">
                                        @foreach($nurses as $nurse => $count)
                                            @if($count['Scheduled'] != 0 && $count['Actual Made'] != 0)
                                                <div class=""><h5>{{$nurse}}</h5>
                                                    <span>Sch: {{$count['Scheduled']}}</span><br/>
                                                    <span>Made: {{$count['Actual Made']}}</span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                                <?php $count++; ?>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@stop