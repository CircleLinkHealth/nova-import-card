<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\CLH\Repositories\CCDImporterRepository;
use App\Console\Commands\ReimportPatientMedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord;
use Illuminate\Http\Request;

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
                $medicalRecord->update([
                    'imported' => false,
                ]);

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
                        $imr['location_id']         = $record['Location'];
                        $imr['practice_id']         = $record['Practice'];
                        $imr['billing_provider_id'] = $record['Billing Provider'];
                        $carePlan                   = $imr->updateOrCreateCarePlan();
                        array_push($importedRecords, [
                            'id'        => $id,
                            'completed' => true,
                            'patient'   => $carePlan->patient()->first(),
                        ]);
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

        return response()->json([
            'message' => 'no records provided',
        ], 400);
    }

    public function importDEPRECATED(Request $request)
    {
        $import = $request->input('medicalRecordsToImport');
        $delete = $request->input('medicalRecordsToDelete');

        if ( ! empty($import)) {
            foreach ($import as $id) {
                $imr = ImportedMedicalRecord::find($id);

                if (empty($imr)) {
                    continue;
                }

                $carePlan = $imr->updateOrCreateCarePlan();

                $imported[] = [
                    'importedMedicalRecordId' => $id,
                    'userId'                  => $carePlan->user_id,
                ];
            }
        }

        if ( ! empty($delete)) {
            $deleted = [];

            foreach ($delete as $id) {
                if (empty($id)) {
                    continue;
                }

                $imr = ImportedMedicalRecord::find($id);

                $medicalRecord = app($imr->medical_record_type)->find($imr->medical_record_id);
                $medicalRecord->update([
                    'imported' => false,
                ]);

                $imr->delete();

                $deleted[] = $id;
            }
        }

        return response()->json(compact('imported', 'deleted'), 200);
    }

    public function importOld(Request $request)
    {
        $import = $request->input('medicalRecordsToImport');
        $delete = $request->input('medicalRecordsToDelete');

        if ( ! empty($import)) {
            foreach ($import as $id) {
                $imr = ImportedMedicalRecord::find($id);

                if (empty($imr)) {
                    continue;
                }

                $carePlan = $imr->updateOrCreateCarePlan();

                $imported[] = [
                    'importedMedicalRecordId' => $id,
                    'userId'                  => $carePlan->user_id,
                ];
            }
        }

        if ( ! empty($delete)) {
            $deleted = [];

            foreach ($delete as $id) {
                if (empty($id)) {
                    continue;
                }

                $imr = ImportedMedicalRecord::find($id);

                $medicalRecord = app($imr->medical_record_type)->find($imr->medical_record_id);
                $medicalRecord->update([
                    'imported' => false,
                ]);

                $imr->delete();

                $deleted[] = $id;
            }
        }

        return response()->json(compact('imported', 'deleted'), 200);
    }

    public function reImportPatient($userId)
    {
        \Artisan::queue(ReimportPatientMedicalRecord::class, [
            'patientUserId' => $userId,
        ]);

        return redirect()->back();
    }
}
