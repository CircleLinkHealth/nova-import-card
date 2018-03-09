<div class="panel panel-default">
    <div class="panel-heading">CarePlan Manager Patient Totals</div>
    <div class="panel-body">
        <table class="table">
            <tr>
                <th><a href="{{route('OpsDashboard.patientList', ['type'=> 'day'])}}" method="GET">Daily</a></th>
                <th><a href="{{route('OpsDashboard.patientList', ['type'=> 'week'])}}" method="GET">Weekly</a></th>
                <th><a href="{{route('OpsDashboard.patientList', ['type'=> 'month'])}}" method="GET">Monthly</a></th>
                <th><a href="{{route('OpsDashboard.patientList', ['type'=> 'total'])}}" method="GET">Total</a></th>
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
            <select name="type">
                <option value="day">Day</option>
                <option value="week">Week</option>
                <option value="month">Month</option>
            </select>

            <input id="dayDate" type="date" name="dayDate" required class="form-control">

            <div class="col-md-4">
                <form action="{{route('OpsDashboard.totalData')}}" method="GET" class="form-inline">
                    <div class="form-group">
                        <label for="dayDate">Go to a specific day</label>
                        <input id="dayDate" type="date" name="dayDate" required class="form-control">
                    </div>

                    <input type="submit" value="Submit" class="btn btn-info">
                </form>
            </div>

            <div class="col-md-4">
                <form action="{{route('OpsDashboard.totalData')}}" method="GET">
                    Go to a specific week:
                    <input type="date" name="weekDate" required class="form-control">
                    <input type="submit" value="Submit" class="btn btn-info">
                </form>
            </div>

            <div class="col-md-4">
                <form action="{{route('OpsDashboard.totalData')}}" method="GET">
                    Go to a specific month:
                    <input type="date" name="monthDate" required class="form-control">
                    <input type="submit" value="Submit" class="btn btn-info">
                </form>
            </div>
        </div>
    </div>
</div>