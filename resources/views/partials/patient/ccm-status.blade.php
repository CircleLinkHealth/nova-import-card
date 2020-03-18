@php
use CircleLinkHealth\Customer\Entities\Patient;
@endphp

@if(Route::is('patient.note.create') || Route::is('patient.note.edit'))
    <li class="inline-block">
        <select id="ccm_status" name="ccm_status" class="selectpickerX dropdownValid form-control"
                data-size="2"
                style="width: 135px">
            <option value="{{Patient::ENROLLED}}" {{$patient->getCcmStatus() == Patient::ENROLLED ? 'selected' : ''}}>
                Enrolled
            </option>
            @if($patient->getCcmStatus() == Patient::WITHDRAWN_1ST_CALL)
                <option class="withdrawn_1st_call"
                        value="{{Patient::WITHDRAWN_1ST_CALL}}" {{$patient->getCcmStatus() == Patient::WITHDRAWN_1ST_CALL ? 'selected' : ''}}>
                    Wthdrn 1st Call
                </option>
            @else
                <option
                        class="withdrawn"
                        value="{{CircleLinkHealth\Customer\Entities\Patient::WITHDRAWN}}" {{$patient->getCcmStatus() == CircleLinkHealth\Customer\Entities\Patient::WITHDRAWN ? 'selected' : ''}}>
                    Withdrawn
                </option>
            @endif
            <option class="paused"
                    value="{{CircleLinkHealth\Customer\Entities\Patient::PAUSED}}" {{$patient->getCcmStatus() == CircleLinkHealth\Customer\Entities\Patient::PAUSED ? 'selected' : ''}}>
                Paused
            </option>
            @if($patient->getCcmStatus() == Patient::UNREACHABLE)
                <option
                        class="unreachable"
                        value="{{CircleLinkHealth\Customer\Entities\Patient::UNREACHABLE}}" {{$patient->getCcmStatus() == CircleLinkHealth\Customer\Entities\Patient::UNREACHABLE ? 'selected' : ''}}>
                    Unreachable
                </option>
            @endif
        </select>
    </li>
@else
    <li style="font-size: 18px"
        class="inline-block col-xs-pull-1 {{$patient->getCcmStatus()}}"><?= (empty($patient->getCcmStatus()))
            ? 'N/A'
            : (CircleLinkHealth\Customer\Entities\Patient::WITHDRAWN_1ST_CALL === $patient->getCcmStatus()
                ? 'Withdrawn 1st Call'
                : ucwords($patient->getCcmStatus())); ?></li>
@endif