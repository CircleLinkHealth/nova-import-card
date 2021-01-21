<?php
//$showApprovalButton = true;
?>
<div class="main-form-progress">
    <div class="row row-centered">
        @if($patient->getCareplanMode() == CircleLinkHealth\SharedModels\Entities\CarePlan::PDF)
            <div class="progress-buttons col-sm-12 col-centered text-center">
                <button id="approve-forward" form="ucpForm" type="submit" class="btn btn-primary btn-next inline-block submitFormBtn">
                    Save
                </button>
            </div>
        @endif

        @if($patient->getCareplanMode() == CircleLinkHealth\SharedModels\Entities\CarePlan::WEB)
            @if(isset($patient->id))
                <div class="progress-buttons col-sm-12 col-centered text-center">

                @if(Route::is('patient.demographics.show'))
                    <!-- <button type="submit" class="btn btn-primary btn-next inline-block">Submit</button> -->
                        @if( isset($showApprovalButton) && $showApprovalButton )
                            <a id="approve-forward" href="#" class="btn btn-primary btn-next inline-block submitFormBtn"
                               dtarget="{{ route('patient.careplan.print', ['patientId' => $patient->id, 'page' => 3]) }}">Approve/Next
                                Page&nbsp; <span class="glyphicon glyphicon-circle-arrow-right"></span></a>
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
                        <p class="">PROGRESS: 1 of 4</p>
                    </div>
                    @endif
                @else
                    <p class="">&nbsp;</p>
                @endif
    </div>
    @endif
    @endif

    @if(!isset($patient->id))
        <div class="progress-buttons col-sm-12 col-centered text-center">
            <a href="{{ route('patients.dashboard', array()) }}" omitsubmit="yes"
               class="btn btn-danger btn-next inline-block omitsubmit">Cancel</a>
            <button type="submit" class="btn btn-primary btn-next inline-block" dusk="unit-test-submit">Add Patient</button>
        </div>
        @endif
</div>
