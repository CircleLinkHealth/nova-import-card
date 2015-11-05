@include('errors.errors')
<div class="edit-navigation-buttons col-lg-10 col-lg-offset-1">
    <div class="btn-group btn-group-justified" role="group" aria-label="...">
        <div class="btn-group" role="group">
            <button type="button" dtarget="{{ URL::route('patient.demographics.show', array('patientId' => $patient->ID)) }}" class="btn btn-primary submitFormBtn @if(Route::is('patient.demographics.show')) active @endif"><span class="btn-number">1</span> <span class="btn-text">Patient Contact</span></button>
        </div>
        <div class="btn-group" role="group">
            <button type="button" dtarget="{{ URL::route('patient.careteam.show', array('patientId' => $patient->ID)) }}" class="btn btn-primary submitFormBtn @if(Route::is('patient.careteam.show')) active @endif"><span class="btn-number">2</span> <span class="btn-text">Patient Care Team</span></button>
        </div>
        <div class="btn-group" role="group">
            <button type="button" dtarget="{{ URL::route('patient.careplan.show', array('patientId' => $patient->ID)) }}" class="btn btn-primary submitFormBtn @if(Route::is('patient.careplan.show')) active @endif" data-toggle="tooltip" data-placement="top" title="Conditions, Lifestyle & Medications Monitors"><span class="btn-number">3</span>  <span class="btn-text">Patient Monitors I</span></button>
        </div>
        <div class="btn-group" role="group">
            <button type="button"  dtarget="{{ URL::route('patient.careplan.show', array('patientId' => $patient->ID)) }}" class="btn btn-primary submitFormBtn @if(Route::is('patient.careplan.show')) active @endif" data-toggle="tooltip" data-placement="top" title="Biometrics and Transitional Care"><span class="btn-number">4</span>  <span class="btn-text">Patient Monitors II</span></button>
        </div>
        <div class="btn-group" role="group">
            <button type="button" dtarget="{{ URL::route('patient.careplan.show', array('patientId' => $patient->ID)) }}" class="btn btn-primary submitFormBtn @if(Route::is('patient.careplan.show')) active @endif"><span class="btn-number">5</span> <span class="btn-text">Symptoms/Other</span></button>
        </div>
    </div>
</div>