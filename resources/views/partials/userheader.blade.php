<div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">
    <div class="row">
        <div class="col-sm-12">
            <p class="text-medium clearfix">
                <span class="pull-left"><strong>
                        <?php
                        $provider = App\User::find($patient->getBillingProviderIDAttribute());
                        ?>
                        @if($provider)
                            Provider: </strong> {{$provider->getFullNameAttribute()}} <strong> 
                        @else
                            Provider: <em>No Provider Selected </em>
                        @endif
                        Location:</strong>
                                <?php (is_null($patient->getPreferredLocationName())) ?  'Not Set' : $patient->getPreferredLocationName();  ?>
                </span>
               <a href="{{URL::route('patient.summary', array('patient' => $patient->ID))}}"><span class="pull-right">{{
                date("F", mktime(0, 0, 0, Carbon\Carbon::now()->month, 10))
                 }} Time: {{gmdate("i:s", $patient->monthlyTime)}}</span></a></p>
            <a href="{{ URL::route('patient.summary', array('patient' => $patient->ID)) }}">
                <span class="person-name text-big text-dark text-serif" title="{{$patient->ID}}">{{$patient->fullName}}</span></a>
            <ul class="person-info-list inline-block text-medium">
                <li class="inline-block">DOB: {{$patient->birthDate}}</li>
                <li class="inline-block">{{$patient->gender}}</li>
                <li class="inline-block">{{$patient->age}} yrs</li>
                <li class="inline-block">{{$patient->phone}}</li>
            </ul>
        </div>
    </div>
    @if($patient->getAgentName())
    <div class="row">
        <div class="col-sm-12">
            <ul class="person-info-listX inline-block text-medium">
                <li class="inline-block">Alternate Contact: <span title="{{$patient->getAgentEmail()}}">({{$patient->getAgentRelationship()}}) {{$patient->getAgentName()}}&nbsp;&nbsp;</span></li>
                <li class="inline-block">{{$patient->getAgentPhone()}}</li>
            {{--</ul><div style="clear:both"></div><ul class="person-conditions-list inline-block text-medium"></ul>--}}
        </div>
    </div>
    @endif
</div>
