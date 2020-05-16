@push('styles')
    <style>
        .medical-record-admin-display {
            display: inline-block;
            padding: 8px 0 8px 2px;
        }

        .medical-record-admin-display a {
            font-size: 21px;
            height: 28px;
            min-width: 25px;
        }

        .medical-record-admin-display a span {
            height: 25px;
            width: 25px;
        }
    </style>
@endpush

<div class="pull-right">
    @if($patient->hasCcda())
        <div class="medical-record-admin-display">
            <a href="{{ route('get.CCDViewerController.showByUserId', [ 'userId' => $patient->id]) }}"
               class="btn btn-default btn-xs tooltip-bottom"
               target="_blank"
               data-tooltip="View latest CCDA"
            >
                <span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>
            </a>
        </div>

        <div class="medical-record-admin-display">
            <a href="{{ route('get.CCDViewerController.exportAllCCDs', [ 'userId' => $patient->id]) }}"
               class="btn btn-default btn-xs tooltip-bottom"
               data-tooltip="Export all CCDAs"
            >
                <span class="glyphicon glyphicon-list-alt" aria-hidden="true"></span>
            </a>
        </div>
    @endif

    @if(auth()->check() && reimportingPatientsIsEnabledForUser(auth()->id()))
        <div class="medical-record-admin-display">
            <a href="{{ route('medical-record.patient.reimport', ['userId' => $patient->id]) }}"
               class="btn btn-default btn-xs tooltip-bottom"
               onclick="return confirm('This will delete and re-import Problems, Medications, Insurances, and Allergies. Would you like to proceed?')"
               data-tooltip="Reimport (beta)"
            >
                <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>
            </a>
        </div>
    @endif
</div>


