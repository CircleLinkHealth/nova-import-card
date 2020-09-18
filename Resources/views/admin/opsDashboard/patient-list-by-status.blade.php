@extends('cpm-admin::partials.adminUI')

@section('content')
    @push('styles')
        <style>
            .ops-dboard-title {
                background-color: #eee;
                padding: 2rem;
            }
        </style>
    @endpush

    <div class="container">
        <div class="col-md-12">
            @include('admin.opsDashboard.panel')
        </div>
        <div class="input-group input-group-sm">
            <article>Active Patients as of 11pm ET on:</article>
            <input id="date" type="date" name="date" value="{{$date->toDateString()}}"required class="form-control">
            <input type="submit" value="Submit" class="btn btn-info">
        </div>
        <div class="col-md-4">
            <div>
                <span>Hours Behind : </span>
                <span class="label label-info">{{$hoursBehind}}</span>
            </div>
        </div>

    </div>
    <div class="panel panel-default">
        {{--<div class="panel-heading">CarePlan Manager Patient Totals for {{$date->toDateString()}}</div>--}}
        <div class="panel-body">
            <table class="table">
                <tr>
                    <th>Active Accounts</th>
                    <th>0 mins</th>
                    <th>0-5</th>
                    <th>5-10</th>
                    <th>10-15</th>
                    <th>15-20</th>
                    <th>20+</th>
                    <th>Total</th>
                    <th>Prior Day totals</th>
                    <th>Added</th>
                    <th>Paused</th>
                    <th>Withdrawn</th>
                    <th>DELTA</th>
                    <th>Backlog/Gcode Hold</th>
                </tr>
                @foreach($rows as $key => $value)
                    <tr>
                        <td>{{$key}}</td>
                        <td>{{$value['ccmCounts']['zero']}}<td>
                        <td>{{$value['ccmCounts']['0to5']}}<td>
                        <td>{{$value['ccmCounts']['5to10']}}<td>
                        <td>{{$value['ccmCounts']['10to15']}}<td>
                        <td>{{$value['ccmCounts']['15to20']}}<td>
                        <td>{{$value['ccmCounts']['20plus']}}<td>
                        <td>{{$value['ccmCounts']['total']}}<td>
                        <td>{{$value['ccmCounts']['priorDayTotals']}}<td>
                        <td>{{$value['countsByStatus']['enrolled']}}<td>
                        <td>{{$value['countsByStatus']['pausedPatients']}}<td>
                        <td>{{$value['countsByStatus']['withdrawnPatients']}}<td>
                        <td>{{$value['countsByStatus']['delta']}}<td>
                        <td>{{$value['countsByStatus']['gCodeHold']}}<td>
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
@endsection


