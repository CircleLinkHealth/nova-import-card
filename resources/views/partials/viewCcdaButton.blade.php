@if($patient->hasCcda())
    <div class="pull-right">
        <div style="padding-left: 15px;">
            <a href="{{ route('get.CCDViewerController.showByUserId', [ 'userId' => $patient->id]) }}"
               class="btn btn-primary btn-xs"
               target="_blank" style="font-size: 15px"
            >
                View Latest CCDA
            </a>
        </div>

        <div style="padding-top: 10px;">
            <a href="{{ route('get.CCDViewerController.exportAllCCDs', [ 'userId' => $patient->id]) }}"
               class="btn btn-warning btn-xs"
               style="font-size: 15px"
            >
                Export all CCDAs
            </a>
        </div>
    </div>
@else
    <div class="">
        <b>CCDA N/A For Patient.</b>
    </div>
@endif

<div class="pull-right" style="padding-top: 10px;">
    <a href="{{ route('medical-record.patient.reimport', ['userId' => $patient->id]) }}"
       class="btn btn-danger btn-xs"
       style="font-size: 15px"
       onclick="return confirm('CPM will search for the most recent medical record and reimport the patient. Only do this if the patient did not import correctly. CPM will notify you once reimporting finishes.')"
    >
        Attempt Reimport
    </a>
</div>