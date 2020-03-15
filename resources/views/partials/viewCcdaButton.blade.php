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
        <div style="padding: 10px 0;">
            <a href="{{ route('medical-record.patient.reimport', ['userId' => $patient->id, 'flushCcd' => 'on']) }}"
               class="btn btn-danger btn-xs"
               style="font-size: 15px"
               onclick="return confirm('This will delete and re-import Problems, Medications, Insurances, and Allergies. Would you like to proceed?')"
            >
                Reimport (beta)
            </a>
        </div>
    @endif
</div>


