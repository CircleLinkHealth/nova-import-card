<div class="main-form-progress">
    <div class="row row-centered">
        @if(isset($patient->ID) )
            <div class="progress-buttons col-sm-12 col-centered text-center">
                @if(Route::is('patient.demographics.show'))
                    <!-- <button type="submit" class="btn btn-primary btn-next inline-block">Submit</button> -->
                    @if( isset($showApprovalButton) && $showApprovalButton )
                        <a href="#" class="btn btn-primary btn-next inline-block submitFormBtn" dtarget="{{ URL::route('patient.careteam.show', array('patientId' => $patient->ID)) }}">Approve/Next Page&nbsp; <span class="glyphicon glyphicon-circle-arrow-right"></span></a>
                    @endif
                @endif
                @if(Route::is('patient.careteam.show'))
                    <a href="#" class="btn btn-primary btn-next inline-block submitFormBtn" dtarget="{{ URL::route('patient.demographics.show', array('patientId' => $patient->ID)) }}"><span class="glyphicon glyphicon-circle-arrow-left"></span></a>
                    <!-- <button type="submit" class="btn btn-primary btn-next inline-block">Submit</button> -->
                    @if( isset($showApprovalButton) && $showApprovalButton )
                        <a href="#" class="btn btn-primary btn-next inline-block submitFormBtn" dtarget="{{ URL::route('patient.careplan.show', array('patientId' => $patient->ID, 'page' => 1)) }}">Approve/Next Page&nbsp; <span class="glyphicon glyphicon-circle-arrow-right"></span></a>
                    @endif
                @endif
                @if(Route::is('patient.careplan.show') && isset($page) && $page == 1)
                    <input type=hidden name=page value="{{ $page }}">
                    <a href="#" class="btn btn-primary btn-next inline-block submitFormBtn" dtarget="{{ URL::route('patient.careteam.show', array('patientId' => $patient->ID)) }}"><span class="glyphicon glyphicon-circle-arrow-left"></span></a>
                    <!-- <button type="submit" class="btn btn-primary btn-next inline-block">Submit</button> -->
                    @if( isset($showApprovalButton) && $showApprovalButton)
                        <a href="#" class="btn btn-primary btn-next inline-block submitFormBtn" dtarget="{{ URL::route('patient.careplan.show', array('patientId' => $patient->ID, 'page' => 2)) }}">Approve/Next Page&nbsp; <span class="glyphicon glyphicon-circle-arrow-right"></span></a>
                    @endif
                @endif
                @if(Route::is('patient.careplan.show') && isset($page) && $page == 2)
                    <input type=hidden name=page value="{{ $page }}">
                    <a href="#" class="btn btn-primary btn-next inline-block submitFormBtn" dtarget="{{ URL::route('patient.careplan.show', array('patientId' => $patient->ID, 'page' => 1)) }}"><span class="glyphicon glyphicon-circle-arrow-left"></span></a>
                    <!-- <button type="submit" class="btn btn-primary btn-next inline-block">Submit</button> -->
                    @if( isset($showApprovalButton) && $showApprovalButton )
                        <a href="#" class="btn btn-primary btn-next inline-block submitFormBtn" dtarget="{{ URL::route('patient.careplan.show', array('patientId' => $patient->ID, 'page' => 3)) }}">Approve/Next Page&nbsp; <span class="glyphicon glyphicon-circle-arrow-right"></span></a>
                    @endif
                @endif
                @if(Route::is('patient.careplan.show') && isset($page) && $page == 3)
                    <input type=hidden name=page value="{{ $page }}">
                    <a href="#" class="btn btn-primary btn-next inline-block submitFormBtn" dtarget="{{ URL::route('patient.careplan.show', array('patientId' => $patient->ID, 'page' => 2)) }}"><span class="glyphicon glyphicon-circle-arrow-left"></span></a>
                    <!-- <button type="submit" class="btn btn-primary btn-next inline-block">Submit</button> -->
                    @if( isset($showApprovalButton) && $showApprovalButton )
                        <a href="#" class="btn btn-primary btn-next inline-block submitFormBtn" dtarget="{{ URL::route('patient.careplan.print', array('patientId' => $patient->ID, 'page' => 3)) }}">Approve/Next Page&nbsp; <span class="glyphicon glyphicon-circle-arrow-right"></span></a>
                    @else
                        <a href="#" class="btn btn-primary btn-next inline-block submitFormBtn" dtarget="{{ URL::route('patient.careplan.print', array('patientId' => $patient->ID, 'page' => 3)) }}">Print Care Plan&nbsp; <span class="glyphicon glyphicon-circle-arrow-right"></span></a>
                    @endif

                @endif
            </div>
                @if(!$user_info)
                    @if(Route::is('patient.demographics.show')) <p class="">
                        <ul class="progress-list col-lg-12">
                            <li class="progress-item progress-first progress-active"></li>
                            <li class="progress-item progress-second"></li>
                            <li class="progress-item progress-third"></li>
                            <li class="progress-item progress-fourth"></li>
                            <li class="progress-item progress-fifth"></li>
                        </ul>
                        <div class="progress-status">
                            <p class="">PROGRESS: 1 of 5</p>
                        </div>
                    @endif
                    @if(Route::is('patient.careteam.show'))
                        <ul class="progress-list col-lg-12">
                            <li class="progress-item progress-first progress-active"></li>
                            <li class="progress-item progress-second progress-active"></li>
                            <li class="progress-item progress-third"></li>
                            <li class="progress-item progress-fourth"></li>
                            <li class="progress-item progress-fifth"></li>
                        </ul>
                        <div class="progress-status">
                            <p class="">PROGRESS: 2 of 5</p>
                        </div>
                    @endif
                    @if(Route::is('patient.careplan.show') && isset($page) && $page == 1)
                        <ul class="progress-list col-lg-12">
                            <li class="progress-item progress-first progress-active"></li>
                            <li class="progress-item progress-second progress-active"></li>
                            <li class="progress-item progress-third progress-active"></li>
                            <li class="progress-item progress-fourth"></li>
                            <li class="progress-item progress-fifth"></li>
                        </ul>
                        <div class="progress-status">
                            <p class="">PROGRESS: 3 of 5</p>
                        </div>
                    @endif
                    @if(Route::is('patient.careplan.show') && isset($page) && $page == 2)
                        <ul class="progress-list col-lg-12">
                            <li class="progress-item progress-first progress-active"></li>
                            <li class="progress-item progress-second progress-active"></li>
                            <li class="progress-item progress-third progress-active"></li>
                            <li class="progress-item progress-fourth progress-active"></li>
                            <li class="progress-item progress-fifth"></li>
                        </ul>
                        <div class="progress-status">
                            <p class="">PROGRESS: 4 of 5</p>
                        </div>
                    @endif
                    @if(Route::is('patient.careplan.show') && isset($page) && $page == 3)
                        <ul class="progress-list col-lg-12">
                            <li class="progress-item progress-first progress-active"></li>
                            <li class="progress-item progress-second progress-active"></li>
                            <li class="progress-item progress-third progress-active"></li>
                            <li class="progress-item progress-fourth progress-active"></li>
                            <li class="progress-item progress-fifth progress-active"></li>
                        </ul>
                        <div class="progress-status">
                            <p class="">PROGRESS: 5 of 5</p>
                        </div>
                    @endif
                @else
                    <p class="">&nbsp;</p>
                @endif
            </div>
        @else
            <div class="progress-buttons col-sm-12 col-centered text-center">
                <button type="submit" class="btn btn-primary btn-next inline-block">Add Patient</button>
            </div>
        @endif
    </div>
</div><!-- /main-form-progress -->