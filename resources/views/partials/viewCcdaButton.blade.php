<div class="pull-right">
    @if($patient->hasCcda())
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
    @endif

    @if(auth()->check() && reimportingPatientsIsEnabledForUser(auth()->id()))
        <div style="padding-top: 10px;">
            <a href="{{ route('medical-record.patient.reimport', ['userId' => $patient->id]) }}"
               class="btn btn-info btn-xs"
               style="font-size: 15px"
               onclick="return confirm('CPM will search for the most recent medical record and reimport the patient. Only do this if the patient did not import correctly. CPM will notify you once reimporting finishes.')"
            >
                Reimport
            </a>
        </div>

        <div style="padding: 10px 0;">
            <a href="{{ route('medical-record.patient.reimport', ['userId' => $patient->id, 'clearCcda' => 'on']) }}"
               class="btn btn-danger btn-xs"
               style="font-size: 15px"
               onclick="return confirm('CPM will clear Problems, Medications, Insurances, and Allergies. You typically want to do that in scenarios such as when Problems are imported wrongly due to CPM having false ICD codes. ICD codes were corrected, and now we want to re-import.')"
            >
                Clear and Reimport
            </a>
        </div>
    @endif
</div>


