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

    public function import(Request $request)
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
