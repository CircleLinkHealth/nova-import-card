@if(App\Models\CCD\Ccda::wherePatientId($patient->id)->exists())
    <div class="pull-right">
        <a href="{{ route('get.CCDViewerController.showByUserId', [ 'userId' => $patient->id]) }}"
           class="btn btn-primary btn-xs"
           target="_blank"
        >
            View CCDA
        </a>
    </div>
@else
    <div class="pull-right">
        <b>CCDA not available for this patient.</b>
    </div>
@endif