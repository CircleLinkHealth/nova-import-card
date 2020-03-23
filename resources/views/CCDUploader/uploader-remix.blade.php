@extends('partials.providerUI')

@section('title', 'CCD Uploader')
@section('activity', 'CCD Uploader')

@section('content')
    <div class="container-fluid" style="padding-top: 3%;">

        <div style="display: none">
            <time-tracker ref="TimeTrackerApp" :info="timeTrackerInfo"
                          :twilio-enabled="@json(config('services.twilio.enabled') && (isset($patient) && $patient->primaryPractice ? $patient->primaryPractice->isTwilioEnabled() : true))"
                          :hide-tracker="true"
                          :override-timeout="{{config('services.time-tracker.override-timeout')}}">
            </time-tracker>
        </div>

        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <ccd-upload ref="ccdUpload"></ccd-upload>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-12">
                @if ($shouldUseNewVersion)
                    <imported-medical-records-management ref="importedMedicalRecordsManagement"></imported-medical-records-management>
                @else
                    <ccd-viewer ref="ccdViewer"></ccd-viewer>
                @endif
            </div>
        </div>
    </div>
@endsection
