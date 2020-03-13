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

<div style="padding-top: 10px;">
    <a href="{{ route('medical-record.patient.reimport', ['userId' => $patient->id]) }}"
       class="btn btn-danger btn-xs"
       style="font-size: 15px"
       onclick="return confirm('CPM will search for the most recent medical record and reimport the patient. Only do this if the patient did not import correctly. After you click ok, there will not be a confirmation message. Check back in 2-3 minutes to see the re-imported patient.')"
    >
        Attempt Reimport
    </a>
</div>