@extends('partials.adminUI')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">Scheduled Calls by Day</div>
                            <form class="text-center" method="get"
                                  action="{{URL::route('admin.reports.nurse.allocation')}}">
                                <button type="submit" value="{{Carbon\Carbon::parse($month)->firstOfMonth()->subMonth(1)->toDateString()}}" style="display:inline-block;" name="previous"> <</button>
                                <h1 class="title"
                                    style="display:inline-block;"> {{$month->format('F, Y')}} </h1>
                                <button value="{{Carbon\Carbon::parse($month)->firstOfMonth()->addMonths(1)->toDateString()}}" style="display:inline-block;" type="submit" name="next"> ></button>
                            </form>
                            <hr>
                            @foreach($data as $date => $nurses)
                                <div class="container"
                                     style="display:inline-block; width: 190px; vertical-align: text-top;">
                                    <h4>{{$date}}</h4>
                                    <div class="">
                                        @foreach($nurses as $nurse => $count)
                                            @if($count != null)
                                                <div class="">
                                                    <b>{{$nurse}}</b>: {{$count}}
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@stop