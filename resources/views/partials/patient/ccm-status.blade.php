@php
use CircleLinkHealth\Customer\Entities\Patient;

$ccmStatus = $patient->getCcmStatus();

$statusesForDropdown = [
//option: value => display
     Patient::ENROLLED => 'Enrolled',
     Patient::PAUSED => 'Paused',
];

//It's either withdrawn first call or withdrawn
if ($ccmStatus == Patient::WITHDRAWN_1ST_CALL){
    $statusesForDropdown[Patient::WITHDRAWN_1ST_CALL] = 'Wthdrn 1st Call';
}else{
    $statusesForDropdown[Patient::WITHDRAWN] = 'Withdrawn';
}

//only add this if patient is already withdrawn.
//We do not want to allow anyone to set status as unreachable
if ($ccmStatus == Patient::UNREACHABLE){
    $statusesForDropdown[Patient::UNREACHABLE] = 'Unreachable';
}

@endphp

@if(Route::is('patient.note.create') || Route::is('patient.note.edit'))
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
    <li style="font-size: 18px"
        class="inline-block col-xs-pull-1 {{$ccmStatus}}">{{(empty($ccmStatus))
            ? 'N/A'
            : (Patient::WITHDRAWN_1ST_CALL === $ccmStatus
                ? 'Withdrawn 1st Call'
                : ucwords($ccmStatus))}}</li>
@endif