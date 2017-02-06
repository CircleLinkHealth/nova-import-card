@extends('partials.adminUI')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">Scheduled Calls by Day</div>
                            <h1 class="title text-center"> {{Carbon\Carbon::now()->format('F, Y')}} </h1>
                            <hr>
                            @foreach($data as $date => $nurses)
                                <div class="container" style="display:inline-block; width: 190px; vertical-align: text-top;">
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