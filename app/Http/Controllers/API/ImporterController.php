<?php

namespace App\Http\Controllers\API;

use App\CLH\Repositories\CCDImporterRepository;
use App\Importer\Models\ItemLogs\DocumentLog;
use App\Importer\Models\ItemLogs\ProviderLog;
use App\Jobs\ImportCsvPatientList;
use App\Jobs\TrainCcdaImporter;
use App\Models\MedicalRecords\Ccda;
use App\Models\MedicalRecords\ImportedMedicalRecord;

class ImporterController extends ApiController
{

    /**
     * @SWG\GET(
     *     path="/ccd-importer/medical-records",
     *     tags={"medical records"},
     *     summary="Get Imported Medical Records",
     *     description="Returns a listing of imported medical records that have not been assigned to a patient yet",
     *     @SWG\Response(
     *         response="default",
     *         description="A listing of medical records"
     *     )
     *   )
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        return ImportedMedicalRecord::whereNull('patient_id')
            ->with('demographics')
            ->with('practice')
            ->with('location')
            ->with('billingProvider')
            ->get()
            ->map(function ($summary) {
                $summary['flag'] = false;

                $providers = $summary->medicalRecord()->providers()->where([
                    ['first_name', '!=', null],
                    ['last_name', '!=', null],
                    ['ml_ignore', '=', false],
                ])->get()->unique(function ($m) {
                    return $m->first_name . $m->last_name;
                });

                if ($providers->count() > 1) {
                    $summary['flag'] = true;
                }

                return $summary;
            })->values();
    }
}
