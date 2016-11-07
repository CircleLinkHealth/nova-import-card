<div class="main-form-progress">
    <div class="row row-centered">
        @if(isset($patient) && !$new_user )
            <div class="progress-buttons col-sm-12 col-centered text-center">
                <a href="#" class="btn btn-green btn-next inline-block submitFormBtn" dtarget="/" omitsubmit="yes">Cancel</a>
                <a href="#" class="btn btn-green btn-next inline-block submitFormBtn"
                   dtarget="{{ URL::route('patient.careteam.show', array('patientId' => $patient->id)) }}">

                </a>
                <button type="submit" class="btn btn-orange">Submit</button>
            </div>
            <ul class="progress-list col-lg-12">
                <li class="progress-item progress-first progress-active"></li>
                <li class="progress-item progress-second"></li>
                <li class="progress-item progress-third"></li>
                <li class="progress-item progress-fourth"></li>
                <li class="progress-item progress-fifth"></li>
            </ul>
            <div class="progress-status">
                @if(!$user_info)
                    @if(Route::is('patient.demographics.show')) <p class="">PROGRESS: 1 of 5</p> @endif
                    @if(Route::is('patient.careteam.show')) <p class="">PROGRESS: 2 of 5</p> @endif
                    @if(Route::is('patient.careplan.show')) <p class="">PROGRESS: 3 of 5</p> @endif
                    @if(Route::is('patient.careplan.show')) <p class="">PROGRESS: 4 of 5</p> @endif
                    @if(Route::is('patient.careplan.show')) <p class="">PROGRESS: 5 of 5</p> @endif
                @else
                    <p class="">&nbsp;</p>
                @endif
            </div>
        @endif
    </div>
</div><!-- /main-form-progress -->