<?php namespace App\Http\Controllers;

use App\CLH\CCD\Importer\ImportManager;
use App\CLH\Repositories\CCDImporterRepository;
use App\Importer\Models\ImportedItems\AllergyImport;
use App\Importer\Models\ImportedItems\DemographicsImport;
use App\Importer\Models\ImportedItems\MedicationImport;
use App\Importer\Models\ImportedItems\ProblemImport;
use App\Models\MedicalRecords\Ccda;
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

                $imr->createCarePlan();

                $allergies = AllergyImport::where('medical_record_type', '=', $imr->medical_record_type)
                    ->where('medical_record_id', '=', $imr->medical_record_id)
                    ->get();

                $demographics = DemographicsImport::where('medical_record_type', '=', $imr->medical_record_type)
                    ->where('medical_record_id', '=', $imr->medical_record_id)
                    ->first();

                $medications = MedicationImport::where('medical_record_type', '=', $imr->medical_record_type)
                    ->where('medical_record_id', '=', $imr->medical_record_id)
                    ->get();

                $problems = ProblemImport::where('medical_record_type', '=', $imr->medical_record_type)
                    ->where('medical_record_id', '=', $imr->medical_record_id)
                    ->get();

                /**
                 * @todo: Figure out what happens to duplicate CCDAs
                 */
//                if ($imr->qaSummary->duplicate_id) {
//                    $user = User::find($imr->qaSummary->duplicate_id);
//
//                    $user->ccdAllergies()->delete();
//                    $user->ccdInsurancePolicies()->delete();
//                    $user->ccdMedications()->delete();
//                    $user->ccdProblems()->delete();
//                    $user->patientCareTeamMembers()->delete();
//                    $user->cpmBloodPressure()->delete();
//                    $user->cpmBloodSugar()->delete();
//                    $user->cpmSmoking()->delete();
//                    $user->cpmWeight()->delete();
//
//                    $user->cpmProblems()->detach();
//                    $user->cpmBiometrics()->detach();
//                    $user->cpmLifestyles()->detach();
//                    $user->cpmMedicationGroups()->detach();
//                    $user->cpmMiscs()->detach();
//                    $user->cpmSymptoms()->detach();
//                } else {
                    $user = $this->repo->createRandomUser($demographics);
//                }

                $importer = new ImportManager($allergies->all(), $demographics, $medications->all(), $problems->all(),
                    $strategies->all(), $user, $imr);
                $importer->import();

                $imported[] = [
                    'ccdaId' => $id,
                    'userId' => $user->id,
                ];

                $imr->imported = true;
                $imr->patient_id = $user->id;
                $imr->save();
            }
        }


        if (!empty($delete)) {
            $deleted = [];

            foreach ($delete as $id) {
                if (empty($id)) {
                    continue;
                }
                Ccda::destroy($id);
                $summary = ImportedMedicalRecord::where('medical_record_type', '=',
                    $imr->medical_record_type)->where('medical_record_id', '=',
                    $imr->medical_record_id)->first();
                if (!empty($summary)) {
                    $summary->delete();
                }

                $deleted[] = $id;
            }
        }

        return response()->json(compact('imported', 'deleted'), 200);
    }

}
