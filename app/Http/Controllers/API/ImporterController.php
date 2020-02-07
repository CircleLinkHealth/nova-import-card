<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\API;

use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord;

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
     *
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
                    return $m->first_name.$m->last_name;
                });

                if ($providers->count() > 1) {
                    $summary['flag'] = true;
                }

                return $summary;
            })->values();
    }
}
