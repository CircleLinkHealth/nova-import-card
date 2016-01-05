<div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">
    <div class="row">
        <div class="col-sm-12">
            <p class="text-medium clearfix">
                <span class="pull-left"><strong>
                        <?php
                            debug($patient->getLeadContactIDAttribute());
                        $provider = App\User::find($patient->getLeadContactIDAttribute());
                        ?>
                        Provider: </strong> {{$provider->getFullNameAttribute()}}<strong>
                        Location:</strong>
                                {{$patient->getPreferredLocationName()}}
                </span>
               <a href="{{URL::route('patient.summary', array('patient' => $patient->ID))}}"><span class="pull-right">{{
                date("F", mktime(0, 0, 0, Carbon\Carbon::now()->month, 10))
                 }} Time: {{gmdate("i:s", $patient->monthlyTime)}}</span></a></p>
            <a href="{{ URL::route('patient.summary', array('patient' => $patient->ID)) }}">
                <span class="person-name text-big text-dark text-serif" title="400">{{$patient->fullName}}</span></a>
            <ul class="person-info-list inline-block text-medium">
                <li class="inline-block">DOB: {{$patient->birthDate}}</li>
                <li class="inline-block">{{$patient->gender}}</li>
                <li class="inline-block">{{$patient->age}} yrs</li>
                <li class="inline-block">{{$patient->phone}}</li>
            </ul>
        </div>
    </div>
</div>
