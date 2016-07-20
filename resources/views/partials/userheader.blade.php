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
                                <?= (empty($patient->getPreferredLocationName())) ?  'Not Set' : $patient->getPreferredLocationName();  ?>
                </span>
                <?php
                    // calculate display, fix bug where gmdate('i:s') doesnt work for > 24hrs
                $seconds = $patient->patientInfo()->first()->cur_month_activity_time;
                $H = floor($seconds / 3600);
                $i = ($seconds / 60) % 60;
                $s = $seconds % 60;
                $monthlyTime = sprintf("%02d:%02d:%02d",$H, $i, $s);
                ?>
               <a href="{{URL::route('patient.activity.providerUIIndex', array('patient' => $patient->ID))}}"><span class="pull-right">{{
                date("F", mktime(0, 0, 0, Carbon\Carbon::now()->month, 10))
                 }} Time: {{ $monthlyTime }}</span></a></p>
            <a href="{{ URL::route('patient.summary', array('patient' => $patient->ID)) }}">
                <span class="person-name text-big text-dark text-serif" title="{{$patient->ID}}">{{$patient->fullName}}</span></a>
            <ul class="person-info-list inline-block text-medium">
                <li class="inline-block">DOB: {{$patient->birthDate}}</li>
                <li class="inline-block">{{$patient->gender}}</li>
                <li class="inline-block">{{$patient->age}} yrs</li>
                <li class="inline-block">{{$patient->phone}}</li>
                <li class=" inline-block {{$patient->ccm_status}}"><?= (empty($patient->ccm_status)) ?  'N/A' : ucwords($patient->ccm_status);  ?></li>
                {{--<li class="inline-block">--}}
                    {{--<select id="status" name="status" class="selectpickerX dropdownValid form-control" data-size="2" style="width: 100px">--}}
                            {{--<option value="" {{$patient->ccm_status == 'enrolled' ? 'selected' : ''}}> Enrolled</option>--}}
                            {{--<option value="" {{$patient->ccm_status == 'withdrawn' ? 'selected' : ''}}> Withdrawn</option>--}}
                            {{--<option value="" {{$patient->ccm_status == 'paused' ? 'selected' : ''}}> Paused</option>--}}
                    {{--</select>--}}
                {{--</li>--}}
            </ul>
        </div>
    </div>
    @if($patient->agentName)
    <div class="row">
        <div class="col-sm-12">
            <ul class="person-info-listX inline-block text-medium">
                <li class="inline-block">Alternate Contact: <span title="{{$patient->agentEmail}}">({{$patient->agentRelationship}}) {{$patient->agentName}}&nbsp;&nbsp;</span></li>
                <li class="inline-block">{{$patient->agentPhone}}</li>
            {{--</ul><div style="clear:both"></div><ul class="person-conditions-list inline-block text-medium"></ul>--}}
        </div>
    </div>
    @endif

    @if(!empty($problems))
        <div style="clear:both"></div>
        <ul class="person-conditions-list inline-block text-medium">
            @foreach($problems as $problem)
                <li class="inline-block"><input type="checkbox" id="item27" name="condition27" value="Active"
                                                checked="checked" disabled="disabled">
                    <label for="condition27"><span> </span>{{$problem}}</label>
                </li>
            @endforeach
        </ul>
    @endif
</div>


