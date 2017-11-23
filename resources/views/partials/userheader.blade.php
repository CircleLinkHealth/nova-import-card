<div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12" style="padding-bottom:9px">
    <div class="row">
        <div class="col-sm-12">
            <div class="col-sm-8" style="line-height: 22px;">
                <span style="font-size: 30px;"> <a
                            href="{{ URL::route('patient.summary', array('patient' => $patient->id)) }}">
                    {{$patient->fullName}}
                    </a> </span>
                @if($ccm_complex)
                    <span id="complex_tag"
                          style="background-color: #ec683e;font-size: 15px; position: relative; top: -7px;"
                          class="label label-warning"> Complex CCM</span>
                    @push('scripts')
                    <script>
                        (function () {
                            // subscribe to jQuery event to know whether the complex-cscm checkbox value has been changed or not
                            var $complexSpan = $("#complex_tag");
                            $(document).on("complex-ccm-form-submit", function (e, status) {
                                if (status) $complexSpan.show();
                                else $complexSpan.hide();
                            })
                        })()
                    </script>
                    @endpush
                @endif
                <a
                        href="{{ URL::route('patient.demographics.show', array('patient' => $patient->id)) }}"><span
                            class="glyphicon glyphicon-pencil" style="margin-right:3px;"></span></a><br/>

                <ul class="inline-block" style="margin-left: -40px; font-size: 16px">
                    <b>
                        <li class="inline-block">{{$patient->birthDate ?? 'N/A'}} <span style="color: #4390b5">•</span>
                        </li>
                        <li class="inline-block">{{$patient->gender ?? 'N/A'}} <span style="color: #4390b5">•</span>
                        </li>
                        <li class="inline-block">{{$patient->age ?? 'N/A'}} yrs <span style="color: #4390b5">•</span>
                        </li>
                        <li class="inline-block">{{(new App\CLH\Helpers\StringManipulation())->formatPhoneNumber($patient->phone) ?? 'N/A'}} </li>
                    </b>
                    <li><span> <b>Provider</b>: {{$provider}}  </span></li>
                    <li><span> <b>Practice</b>: {{$patient->primaryProgramName()}} </span></li>
                    @if($patient->agentName)
                        <li class="inline-block"><b>Alternate Contact</b>: <span
                                    title="{{$patient->agentEmail}}">({{$patient->agentRelationship}}
                                ) {{$patient->agentName}} {{$patient->agentPhone}}</span></li>
                        <li class="inline-block"></li>
                    @endif
                </ul>

            </div>
            <div class="col-sm-4" style="line-height: 22px; text-align: right">

                <a href="{{ empty($patient->id) ?: route('patient.activity.providerUIIndex', ['patient' => $patient->id]) }}">
                <span style="font-size: 27px;{{$ccm_above ? 'color: #47beab;' : ''}}">
                    <span data-monthly-time="{{$monthlyTime}}" style="color: inherit">
                        @if (auth()->user()->isCCMCountable())
                            @if (isset($disableTimeTracking) && $disableTimeTracking)
                                <div class="color-grey">{{$monthlyTime}}</div>
                            @else
                                <time-tracker ref="TimeTrackerApp" :info="timeTrackerInfo"></time-tracker>
                            @endif
                        @else
                            <div class="color-grey">{{$monthlyTime}}</div>
                        @endif
                    </span>
                </span>
                </a>

                <span style="font-size:15px"></span><br/>

                @if(Route::is('patient.note.create'))
                    <li class="inline-block">
                        <select id="status" name="status" class="selectpickerX dropdownValid form-control" data-size="2"
                                style="width: 135px">
                            <option style="color: #47beab"
                                    value="enrolled" {{$patient->ccm_status == 'enrolled' ? 'selected' : ''}}> Enrolled
                            </option>
                            <option class="withdrawn"
                                    value="withdrawn" {{$patient->ccm_status == 'withdrawn' ? 'selected' : ''}}>
                                Withdrawn
                            </option>
                            <option class="paused"
                                    value="paused" {{$patient->ccm_status == 'paused' ? 'selected' : ''}}> Paused
                            </option>
                        </select>
                    </li>
                @else
                    <li style="font-size: 18px" id="status"
                        class="inline-block {{$patient->ccm_status}}"><?= (empty($patient->ccm_status))
                            ? 'N/A'
                            : ucwords($patient->ccm_status);  ?></li>
                @endif
                <br/>
                @if(auth()->user()->hasRole(['administrator']))
                    @include('partials.viewCcdaButton')
                @endif

            </div>

        </div>
        @if(!empty($problems))
            <div style="clear:both"></div>
            <ul id="user-header-problems-checkboxes" class="person-conditions-list inline-block text-medium"
                style="margin-top: -10px">
                @foreach($problems as $problem)
                    @if($problem != App\Models\CPM\CpmMisc::OTHER_CONDITIONS && $problem != 'Diabetes')
                        <li class="inline-block"><input type="checkbox" id="item27" name="condition27" value="Active"
                                                        checked="checked" disabled="disabled">
                            <label for="condition27"><span> </span>{{$problem}}</label>
                        </li>
                    @endif
                @endforeach
            </ul>
        @endif
    </div>
</div>

<meta name="is_ccm_complex" content="{{$ccm_complex}}">

@push('scripts')
<script>
    $(document).ready(function () {

        if ($('meta[name="is_ccm_complex"]').attr('content')) {
            $("#complex_tag").show();
        } else {
            $("#complex_tag").hide();
        }

    });

</script>
@endpush

@push('styles')
    <style>
        .color-grey {
            color: #7b7d81;
        }
    </style>
@endpush



