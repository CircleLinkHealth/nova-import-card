@extends('cpm-admin::partials.adminUI')

@section('content')
    @push('styles')
        <style>
            .ops-dboard-title {
                background-color: #eee;
                padding: 2rem;
            }
            .nav-tabs > li, .nav-pills > li {
                float:none;
                display:inline-block;
                *display:inline; /* ie7 fix */
                zoom:1; /* hasLayout ie7 trigger */
            }

            .nav-tabs, .nav-pills {
                text-align:center;
            }

            /*.table td {*/
                /*text-align: center;*/
            /*}*/
        </style>
    @endpush

    <div class="container">
        <div class="col-md-12">
            @include('admin.opsDashboard.panel')
        </div>
        <div class="input-group input-group-sm">
            <div>
                <form action="{{route('OpsDashboard.lostAdded')}}">
                    <div class="form-group">
                        <p>Time frame for Added/Paused/Withdrawn/DELTA:</p>
                        From:
                        <input type="date" name="fromDate" value="{{$fromDate->toDateString()}}" max="{{$maxDate->copy()->subDay(1)->toDateString()}}"required>
                        To:
                        <input type="date" name="toDate" value="{{$toDate->toDateString()}}" max="{{$maxDate->toDateString()}}" required>
                    </div>
                    <div class="form-group">
                        <input align="center" type="submit" value="Submit" class="btn btn-info">
                    </div>
                </form>
            </div>
        </div>

    </div>
    <div class="container">
        <div class="panel panel-default">
            {{--<div class="panel-heading">CarePlan Manager Patient Totals for {{$date->toDateString()}}</div>--}}
            <div class="panel-body">
                <table class="table table-striped table-bordered table-curved table-condensed table-hover">
                    <tr>
                        <th>Active Accounts</th>
                        <th>Added</th>
                        <th>Paused</th>
                        <th>Withdrawn</th>
                        <th>DELTA</th>
                    </tr>
                    @foreach($rows as $key => $value)
                        <tr>
                            <th>{{$key}}</th>
                            <td>{{$value['Added']}}</td>
                            <td>{{$value['Paused']}}</td>
                            <td>{{$value['Withdrawn']}}</td>
                            <td>{{$value['Delta']}}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-md-4">
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection


