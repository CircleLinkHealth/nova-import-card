<?php namespace App\Http\Controllers;

use App\CLH\Repositories\CCDImporterRepository;
use App\Models\MedicalRecords\ImportedMedicalRecord;
use Illuminate\Http\Request;

class MedicalRecordImportController extends Controller
{
    private $repo;

    public function __construct(CCDImporterRepository $repo)
    {
        $this->repo = $repo;
    }

    public function deleteRecords(Request $request) {
        $recordsToDelete = explode(',', $request->input('records'));
        $recordsNotFound = [];

        foreach ($recordsToDelete as $id) {
            if (empty($id)) {
                continue;
            }

            $imr = ImportedMedicalRecord::find($id);

            if ($imr) {
                $medicalRecord = app($imr->medical_record_type)->find($imr->medical_record_id);
                $medicalRecord->update([
                    'imported' => false
                ]);
    
                $imr->delete();
            }
            else {
                array_push($recordsNotFound, $id);
                array_splice($recordsToDelete, array_search($id, $recordsToDelete));
            }
        }

        return response()->json([ 'deleted' => $recordsToDelete, 'not_found' => $recordsNotFound ], 200);
    }

    public function import(Request $request) {
        //$recordsToImport = $request->all();

        // if (is_array($recordsToImport)) {
        //     foreach($recordsToImport as $record) {
        //         $imr = ImportedMedicalRecord::find($record->id);
        //         if (empty($imr)) continue;
        //         else {
        //             return response()->json($imr, 200);
        //         }
        //     }
        // }
        return response()->json([], 200);
    }

    public function importOld(Request $request)
    {
        $import = $request->input('medicalRecordsToImport');
        $delete = $request->input('medicalRecordsToDelete');


        if (!empty($import)) {
            foreach ($import as $id) {
                $imr = ImportedMedicalRecord::find($id);

                if (empty($imr)) {
                    continue;
                }

                $carePlan = $imr->createCarePlan();

                $imported[] = [
                    'importedMedicalRecordId' => $id,
                    'userId'                  => $carePlan->user_id,
                ];
            }
        }


        if (!empty($delete)) {
            $deleted = [];

            foreach ($delete as $id) {
                if (empty($id)) {
                    continue;
                }

                $imr = ImportedMedicalRecord::find($id);

                $medicalRecord = app($imr->medical_record_type)->find($imr->medical_record_id);
                $medicalRecord->update([
                    'imported' => false
                ]);

                $imr->delete();

                $deleted[] = $id;
            }
        }

        return response()->json(compact('imported', 'deleted'), 200);
    }
}
