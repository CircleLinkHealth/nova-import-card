<div class="panel panel-default">
    <div class="panel-heading">Patient Stats by Practice</div>
    <div class="panel-body">
        <table class="table">
            <tr>
                <th>Daily</th>
                <th>Weekly</th>
                <th>Monthly</th>
                <th>Total</th>
            </tr>
            <tr>
                <td>Enrolled: {{$patientsByPractice['dayCount']['enrolled']}}</td>
                <td>Enrolled: {{$patientsByPractice['weekCount']['enrolled']}}</td>
                <td>Enrolled: {{$patientsByPractice['monthCount']['enrolled']}}</td>
                <td>Enrolled: {{$patientsByPractice['totalCount']['enrolled']}}</td>
            </tr>
            <tr>
                <td>G0506 Hold: {{$patientsByPractice['dayCount']['gCodeHold']}}</td>
                <td>G0506 Hold: {{$patientsByPractice['weekCount']['gCodeHold']}}</td>
                <td>G0506 Hold: {{$patientsByPractice['monthCount']['gCodeHold']}}</td>
                <td>G0506 Hold: {{$patientsByPractice['totalCount']['gCodeHold']}}</td>
            </tr>
            <tr>
                <td>Paused: {{$patientsByPractice['dayCount']['pausedPatients']}}</td>
                <td>Paused: {{$patientsByPractice['weekCount']['pausedPatients']}}</td>
                <td>Paused: {{$patientsByPractice['monthCount']['pausedPatients']}}</td>
                <td>Paused: {{$patientsByPractice['totalCount']['pausedPatients']}}</td>
            </tr>
            <tr>
                <td>Withdrawn: {{$patientsByPractice['dayCount']['withdrawnPatients']}} </td>
                <td>Withdrawn: {{$patientsByPractice['weekCount']['withdrawnPatients']}}</td>
                <td>Withdrawn: {{$patientsByPractice['monthCount']['withdrawnPatients']}}</td>
                <td>Withdrawn: {{$patientsByPractice['totalCount']['withdrawnPatients']}}</td>
            </tr>
        </table>
    </div>
    <div class="panel-footer">
        <div class="row">
            <div class="col-md-4">
                <form action="{{route('OpsDashboard.patientsByPractice')}}" method="GET" class="form-inline">
                    <div class="form-group">
                        <select name="practice_id">
                            <option name= "practice_id" value="">Active Practices</option>
                            @foreach($practices as $practice)
                                <option  value="{{$practice['id']}}" required>{{$practice->display_name}}</option>
                            @endforeach
                        </select>
                        <select name="type">
                            <option name="type" value="day">Day</option>
                            <option name="type" value="week">Week</option>
                            <option name="type" value="month">Month</option>
                        </select>
                        <input id="date" type="date" name="date" required class="form-control">
                        <input type="submit" value="Submit" class="btn btn-info">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>