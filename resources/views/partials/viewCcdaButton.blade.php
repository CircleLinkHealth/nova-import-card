@if(App\Models\MedicalRecords\Ccda::wherePatientId($patient->id)->exists())
    <div class="pull-right">
        <a href="{{ route('get.CCDViewerController.showByUserId', [ 'userId' => $patient->id]) }}"
           class="btn btn-primary btn-xs"
           target="_blank" style="font-size: 15px"
        >
            View CCDA
        </a>
    </div>
@else
    <div class="">
        <b>CCDA N/A For Patient.</b>
    </div>
@endif