@php
use CircleLinkHealth\Customer\Entities\Patient;

$ccmStatus = $patient->getCcmStatus();

@endphp

@if(Route::is('patient.note.create') || Route::is('patient.note.edit'))
    <li class="inline-block">
        <select id="ccm_status" name="ccm_status" class="selectpickerX dropdownValid form-control"
                data-size="2"
                style="width: 135px">
            <option value="{{Patient::ENROLLED}}" {{$ccmStatus == Patient::ENROLLED ? 'selected' : ''}}>
                Enrolled
            </option>
            @if($ccmStatus == Patient::WITHDRAWN_1ST_CALL)
                <option class="withdrawn_1st_call"
                        value="{{Patient::WITHDRAWN_1ST_CALL}}" {{$ccmStatus == Patient::WITHDRAWN_1ST_CALL ? 'selected' : ''}}>
                    Wthdrn 1st Call
                </option>
            @else
                <option
                        class="withdrawn"
                        value="{{Patient::WITHDRAWN}}" {{$ccmStatus == Patient::WITHDRAWN ? 'selected' : ''}}>
                    Withdrawn
                </option>
            @endif
            <option class="paused"
                    value="{{Patient::PAUSED}}" {{$ccmStatus == Patient::PAUSED ? 'selected' : ''}}>
                Paused
            </option>
            @if($patient->getCcmStatus() == Patient::UNREACHABLE)
                <option
                        class="unreachable"
                        value="{{Patient::UNREACHABLE}}" {{$ccmStatus == Patient::UNREACHABLE ? 'selected' : ''}}>
                    Unreachable
                </option>
            @endif
        </select>
    </li>
@else
    <li style="font-size: 18px"
        class="inline-block col-xs-pull-1 {{$ccmStatus}}"><?= (empty($ccmStatus))
            ? 'N/A'
            : (Patient::WITHDRAWN_1ST_CALL === $ccmStatus
                ? 'Withdrawn 1st Call'
                : ucwords($ccmStatus)); ?></li>
@endif