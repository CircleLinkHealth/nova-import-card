@if(Route::is('patient.note.create') || Route::is('patient.note.edit'))
    <div id="header-withdrawn-reason" class="hidden" style="padding-top: 10px;">

        <div class="col-md-12"
             style="padding-right: 0 !important;">{!! Form::label('withdrawn_reason', 'Withdrawn Reason: ') !!}</div>
        <div class="col-sm-12" style="padding-right: 0 !important;">
            <div id="header-perform-reason-select">

                {!! Form::select('withdrawn_reason', $withdrawnReasons, ! array_key_exists($patientWithdrawnReason, $withdrawnReasons) && $patientWithdrawnReason != null ? 'Other' : $patientWithdrawnReason, ['class' => 'selectpickerX dropdownValid form-control', 'style' => 'width:100%;']) !!}

            </div>
        </div>
    </div>
    <div id="header-withdrawn-reason-other" class="form-group hidden">
        <div class="col-md-12" style="padding-right: 0 !important;">
            <div>
                                <textarea id="withdrawn_reason_other" rows="1" cols="100" style="resize: none;"
                                          placeholder="Enter Reason..." name="withdrawn_reason_other"
                                          required="required"
                                          class="form-control">{{! array_key_exists($patientWithdrawnReason, $withdrawnReasons) ? $patientWithdrawnReason : null}}</textarea>
            </div>
        </div>
    </div>
@endif