<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\CLH\Repositories\CCDImporterRepository;
use CircleLinkHealth\Eligibility\Console\ReimportPatientMedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class MedicalRecordImportController extends Controller
{
    private $repo;

    public function __construct(CCDImporterRepository $repo)
    {
        $this->repo = $repo;
    }

    public function deleteRecords(Request $request)
    {
        $recordsToDelete = explode(',', $request->input('records'));
        $recordsNotFound = [];

        foreach ($recordsToDelete as $id) {
            if (empty($id)) {
                continue;
            }

            $imr = ImportedMedicalRecord::find($id);

            if ($imr) {
                $medicalRecord = $imr->medicalRecord();
                $medicalRecord->update(
                    [
                        'imported' => false,
                    ]
                );

                $imr->delete();
            } else {
                array_push($recordsNotFound, $id);
                array_splice($recordsToDelete, array_search($id, $recordsToDelete));
            }
        }

        return response()->json(['deleted' => $recordsToDelete, 'not_found' => $recordsNotFound], 200);
    }

    public function import(Request $request)
    {
        $recordsToImport = $request->all();

        if (is_array($recordsToImport)) {
            $importedRecords = [];
            foreach ($recordsToImport as $record) {
                if ($record) {
                    $id  = $record['id'];
                    $imr = ImportedMedicalRecord::find($id);
                    if (empty($imr)) {
                        continue;
                    }
                    try {
                        $imr['location_id']         = $record['location_id'];
                        $imr['practice_id']         = $record['practice_id'];
                        $imr['billing_provider_id'] = $record['billing_provider_id'];
                        $imr['nurse_user_id']       = $record['nurse_user_id'] ?? null;
                        $carePlan                   = $imr->updateOrCreateCarePlan();
                        array_push(
                            $importedRecords,
                            [
                                'id'        => $id,
                                'completed' => true,
                                'patient'   => $carePlan->patient()->first(),
                            ]
                        );
                        $imr->imported = true;
                        $imr->save();
                    } catch (\Exception $ex) {
                        //throwing Exceptions to help debug importing issues
                        throw $ex;
//                            array_push($importedRecords, [
//                                'id' => $id,
//                                'completed' => false,
//                                'error' => $ex->getMessage()
//                            ]);
                    }
                }
            }

            return response()->json($importedRecords, 200);
        }

        return response()->json(
            [
                'message' => 'no records provided',
            ],
            400
        );
    }
    
    public function reImportPatient(Request $request, $userId)
    {
        $args = [
            'patientUserId'   => $userId,
            'initiatorUserId' => auth()->id(),
        ];

        if ('on' === $request->input('flushCcd')) {
            $args['--flush-ccd'] = true;
        }

        Artisan::queue(
            ReimportPatientMedicalRecord::class,
            $args
        );

        return redirect()->back();
    }
}
