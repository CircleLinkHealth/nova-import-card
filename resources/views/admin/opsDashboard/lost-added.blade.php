@extends('partials.adminUI')

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
            <div>
                <form action="{{route('OpsDashboard.lostAdded')}}">
                    <br>
                    <p>Time frame for Added/Paused/Withdrawn/DELTA:</p>
                    From:
                    <input type="date" name="fromDate" value="{{$fromDate->toDateString()}}" required>
                    To:
                    <input type="date" name="toDate" value="{{$toDate->toDateString()}}" required>

                    <br>
                    <input align="center" type="submit" value="Submit">
                </form>
            </div>
        </div>

    </div>
    <div class="panel panel-default">
        {{--<div class="panel-heading">CarePlan Manager Patient Totals for {{$date->toDateString()}}</div>--}}
        <div class="panel-body">
            <table class="table">
                <tr>
                    <th>Active Accounts</th>
                    <th>Added</th>
                    <th>Paused</th>
                    <th>Withdrawn</th>
                    <th>DELTA</th>
                </tr>
                @foreach($rows as $key => $value)
                    <tr>
                        <td>{{$key}}</td>
                        <td>{{$value['enrolled']}}</td>
                        <td>{{$value['pausedPatients']}}</td>
                        <td>{{$value['withdrawnPatients']}}</td>
                        <td>{{$value['delta']}}</td>
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


