<div class="edit-navigation-buttons col-lg-10 col-lg-offset-1">
    @include('core::partials.errors.errors')
    <br/>
    <div class="btn-group btn-group-justified" role="group" aria-label="...">
        <div class="btn-group" role="group">
            <button type="button"
                    dtarget="{{ route('patient.demographics.show', array('patientId' => $patient->id)) }}"
                    class="btn btn-primary submitFormBtn @if(Route::is('patient.demographics.show')) active @endif">
                <span class="btn-number">1</span> <span class="btn-text">Patient Profile</span></button>
        </div>
    </div>
</div>