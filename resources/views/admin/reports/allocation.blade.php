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

                                        echo '<div class="container"style="display:inline-block; width: 190px; vertical-align: text-top;">
                                                  <h4 style="color: dimgray">' . $d . '</h4>
                                             </div>';
                                    }
                                }

                                ?>

                                <div class="container"
                                     style="display:inline-block; width: 190px; vertical-align: text-top;">
                                    <h4><b>{{$date}}</b></h4>
                                    <div class="">
                                        @foreach($nurses as $nurse => $count)
                                            @if($count != null)
                                                <div class="">
                                                    <h5>{{$nurse}}: {{$count}}</h5>
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