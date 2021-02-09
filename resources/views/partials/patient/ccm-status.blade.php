@if(Route::is('patient.note.create') || Route::is('patient.note.edit'))
    @php
        $model =  \CircleLinkHealth\Customer\Entities\Patient::class;

        $ccmStatus = $patient->getCcmStatus();

        $statusesForDropdown = [
             $model::ENROLLED => 'Enrolled',
        ];

        if (auth()->user()->isAdmin()) {
            $statusesForDropdown[$model::PAUSED] = 'Paused';
        }

        if ($ccmStatus == $model::WITHDRAWN_1ST_CALL){
            $statusesForDropdown[$model::WITHDRAWN_1ST_CALL] = 'Wthdrn 1st Call';
        }else{
            $statusesForDropdown[$model::WITHDRAWN] = 'Withdrawn';
        }

        if ($ccmStatus == $model::UNREACHABLE){
            $statusesForDropdown[$model::UNREACHABLE] = 'Unreachable';
        }
    @endphp
    <li class="inline-block">
        <select id="ccm_status" name="ccm_status" class="selectpickerX dropdownValid form-control"
                data-size="2"
                style="width: 135px">
            @foreach($statusesForDropdown as $value => $display)
                <option class="{{$value}}" value="{{$value}}" {{$ccmStatus == $value ? 'selected' : ''}}>
                    {{$display}}
                </option>
            @endforeach
        </select>
    </li>
@else
    @php
        $model =  \CircleLinkHealth\Customer\Entities\Patient::class;
        $ccmStatus = $patient->getCcmStatus();
        if ($ccmStatus === $model::UNREACHABLE && $patient->isSurveyOnly()){
            $ccmStatus = 'Enrollee';
        }

    @endphp
    <li style="font-size: 18px"
        class="inline-block col-xs-pull-1 {{$ccmStatus}}">{{(empty($ccmStatus))
            ? 'N/A'
            : ($model::WITHDRAWN_1ST_CALL === $ccmStatus
                ? 'Withdrawn 1st Call'
                : ucwords($ccmStatus))}}</li>
@endif
















































































































































