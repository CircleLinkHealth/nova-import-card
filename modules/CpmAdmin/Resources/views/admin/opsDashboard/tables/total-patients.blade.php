<div class="panel panel-default">
    <div class="panel-heading">CarePlan Manager Patient Totals for {{$date->toDateString()}} (Specific {{$dateType}})</div>
    <div class="panel-body">
        <table class="table">
            <tr>
                <th><a href="{{route('OpsDashboard.patientList', ['type'=> 'day', 'date' => $date, 'dateType' => $dateType])}}" method="GET">Daily</a></th>
                <th><a href="{{route('OpsDashboard.patientList', ['type'=> 'week', 'date' => $date, 'dateType' => $dateType])}}" method="GET">Weekly</a></th>
                <th><a href="{{route('OpsDashboard.patientList', ['type'=> 'month', 'date' => $date, 'dateType' => $dateType ])}}" method="GET">Monthly</a></th>
                <th><a href="{{route('OpsDashboard.patientList', ['type'=> 'total', 'date' => $date,  'dateType' => $dateType])}}" method="GET">Total</a></th>
            </tr>
            <tr>
                <td>Enrolled: {{$totals['dayCount']['enrolled']}}</td>
                <td>Enrolled: {{$totals['weekCount']['enrolled']}}</td>
                <td>Enrolled: {{$totals['monthCount']['enrolled']}}</td>
                <td>Enrolled: {{$totals['totalCount']['enrolled']}}</td>
            </tr>
            <tr>
                <td>G0506 Hold: {{$totals['dayCount']['gCodeHold']}}</td>
                <td>G0506 Hold: {{$totals['weekCount']['gCodeHold']}}</td>
                <td>G0506 Hold: {{$totals['monthCount']['gCodeHold']}}</td>
                <td>G0506 Hold: {{$totals['totalCount']['gCodeHold']}}</td>
            </tr>
            <tr>
                <td>Paused: {{$totals['dayCount']['pausedPatients']}}</td>
                <td>Paused: {{$totals['weekCount']['pausedPatients']}}</td>
                <td>Paused: {{$totals['monthCount']['pausedPatients']}}</td>
                <td>Paused: {{$totals['totalCount']['pausedPatients']}}</td>
            </tr>
            <tr>
                <td>Withdrawn: {{$totals['dayCount']['withdrawnPatients']}} </td>
                <td>Withdrawn: {{$totals['weekCount']['withdrawnPatients']}}</td>
                <td>Withdrawn: {{$totals['monthCount']['withdrawnPatients']}}</td>
                <td>Withdrawn: {{$totals['totalCount']['withdrawnPatients']}}</td>
            </tr>
        </table>
    </div>
    <div class="panel-footer">
        <div class="row">
            <div class="col-md-4">
                <form id="total-patients" action="{{route('OpsDashboard.totalData')}}" method="GET" class="form-inline">
                    <div class="form-group">
                        <select name="type">
                            <option name="type" value="day">Day</option>
                            <option name="type" value="week">Week</option>
                            <option name="type" value="month">Month</option>
                        </select>
                        <input id="date" type="date" name="date" value="{{$date->toDateString()}}"required class="form-control">
                        <input type="submit" value="Submit" class="btn btn-info">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>