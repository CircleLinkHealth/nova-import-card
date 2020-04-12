<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\CLH\Repositories\CCDImporterRepository;
use CircleLinkHealth\Eligibility\Console\ReimportPatientMedicalRecord;
use CircleLinkHealth\SharedModels\Entities\Ccda;
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

            $ccda = Ccda::find($id);

            if ($ccda) {
                $ccda->delete();
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
                    $ccda = Ccda::find($id);
                    if (empty($ccda)) {
                        continue;
                    }

                        $ccda['location_id']         = $record['location_id'];
                        $ccda['practice_id']         = $record['practice_id'];
                        $ccda['billing_provider_id'] = $record['billing_provider_id'];
                        $ccda['nurse_user_id']       = $record['nurse_user_id'] ?? null;
                        $carePlan                   = $ccda->updateOrCreateCarePlan();
                        array_push(
                            $importedRecords,
                            [
                                'id'        => $id,
                                'completed' => true,
                                'patient'   => $carePlan->patient()->first(),
                            ]
                        );
                        $ccda->imported = true;
                        $ccda->save();
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
        
        Artisan::queue(
            ReimportPatientMedicalRecord::class,
            $args
        );

        return redirect()->back();
    }
}
