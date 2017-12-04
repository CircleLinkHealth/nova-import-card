<?php
    $patientCarePlan = isset($patient) ? $patient->carePlan : null;
    $patientCarePlanPdfs = isset($patientCarePlan) ? $patientCarePlan->pdfs : null;
    $patientCarePlanPdfsHasItems = isset($patientCarePlanPdfs) ? $patientCarePlanPdfs->count() > 0 : false;
?>

@if(optional($patientCarePlan)->provider_approver_id && optional($patientCarePlan)->provider_date)
    <div class="col-xs-12">
        <div class="pull-right print-row text-right">
            Approved on {{optional($patientCarePlan)->provider_date->format('m/d/Y')}}
            at {{optional($patientCarePlan)->provider_date->setTimezone($patient->timezone ?? 'America/New_York')->format('g:i A')}} {{optional($patientCarePlan)->provider_date->setTimezone($patient->timezone ?? 'America/New_York')->format('T')}}
            by {{App\User::withTrashed()->find(optional($patientCarePlan)->provider_approver_id)->fullName}}
        </div>
    </div>
@endif

