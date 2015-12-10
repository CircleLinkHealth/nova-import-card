<div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">
    <div class="row">
        <div class="col-sm-12">
            <p class="text-medium clearfix">
                <span class="pull-left">Patient Name:</span><a href="#"><span class="pull-right">{{
                date("F", mktime(0, 0, 0, Carbon\Carbon::now()->month, 10))
                 }} Time: {{$patient->getMonthlyTime()}}</span></a></p>
            <a href="{{ URL::route('patient.summary', array('patient' => $patient->ID)) }}">
                <span class="person-name text-big text-dark text-serif" title="400">{{$patient->getFullNameAttribute()}}</span></a>
            <ul class="person-info-list inline-block text-medium">
                <li class="inline-block">DOB: {{$patient->getDOB()}}</li>
                <li class="inline-block">{{$patient->getGender()}}</li>
                <li class="inline-block">{{$patient->getAge()}} yrs</li>
                <li class="inline-block">{{$patient->getPhone()}}</li>
            </ul>
        </div>
    </div>
</div>