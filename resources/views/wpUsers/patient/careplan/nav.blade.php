<div class="edit-navigation-buttons col-lg-10 col-lg-offset-1">
    {{-- $patient->ccmStatus . ' | ' . $patient->careplanStatus --}}
    @include('errors.errors')
    <br />
    <div class="btn-group btn-group-justified" role="group" aria-label="...">
        <div class="btn-group" role="group">
            <button type="button"
                    dtarget="{{ URL::route('patient.demographics.show', array('patientId' => $patient->id)) }}"
                    class="btn btn-primary submitFormBtn @if(Route::is('patient.demographics.show')) active @endif">
                <span class="btn-number">1</span> <span class="btn-text">Patient Profile</span></button>
        </div>
        <div class="btn-group" role="group">
            <button type="button"
                    dtarget="{{ URL::route('patient.careteam.show', array('patientId' => $patient->id)) }}"
                    class="btn btn-primary submitFormBtn @if(Route::is('patient.careteam.show')) active @endif"><span
                        class="btn-number">2</span> <span class="btn-text">Patient Care Team</span></button>
        </div>
        <div class="btn-group" role="group">
            <button type="button"
                    dtarget="{{ URL::route('patient.careplan.show', array('patientId' => $patient->id, 'page' => 1)) }}"
                    class="btn btn-primary submitFormBtn @if(Route::is('patient.careplan.show') && isset($page) && $page == 1) active @endif"
                    data-toggle="tooltip" data-placement="top" title="Conditions, Lifestyle & Medications Monitors">
                <span class="btn-number">3</span> <span class="btn-text">Patient Monitors I</span></button>
        </div>
        <div class="btn-group" role="group">
            <button type="button"
                    dtarget="{{ URL::route('patient.careplan.show', array('patientId' => $patient->id, 'page' => 2)) }}"
                    class="btn btn-primary submitFormBtn @if(Route::is('patient.careplan.show') && isset($page) && $page == 2) active @endif"
                    data-toggle="tooltip" data-placement="top" title="Biometrics and Transitional Care"><span
                        class="btn-number">4</span> <span class="btn-text">Patient Monitors II</span></button>
        </div>
        <div class="btn-group" role="group">
            <button type="button"
                    dtarget="{{ URL::route('patient.careplan.show', array('patientId' => $patient->id, 'page' => 3)) }}"
                    class="btn btn-primary submitFormBtn @if(Route::is('patient.careplan.show') && isset($page) && $page == 3) active @endif">
                <span class="btn-number">5</span> <span class="btn-text">Symptoms/Other</span></button>
        </div>
    </div>
</div>