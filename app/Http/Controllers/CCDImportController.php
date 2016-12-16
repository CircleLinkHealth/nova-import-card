<?php namespace App\Http\Controllers;

use App\CLH\CCD\ImportedItems\AllergyImport;
use App\CLH\CCD\ImportedItems\DemographicsImport;
use App\CLH\CCD\ImportedItems\MedicationImport;
use App\CLH\CCD\ImportedItems\ProblemImport;
use App\CLH\CCD\Importer\ImportManager;
use App\CLH\Repositories\CCDImporterRepository;
use App\Models\CCD\Ccda;
use App\Models\CCD\CcdVendor;
use App\Models\CCD\QAImportSummary;
use App\User;
use Illuminate\Http\Request;

class CCDImportController extends Controller
{
    private $repo;

    public function __construct(CCDImporterRepository $repo)
    {
        $this->repo = $repo;
    }

    public function import(Request $request)
    {
        $ccdsToImport = $request->input('ccdsToImport');
        $ccdsToDelete = $request->input('ccdsToDelete');


        if (!empty($ccdsToImport)) {
            foreach ($ccdsToImport as $id) {
                $ccda = Ccda::find($id);

                if (empty($ccda)) continue;

                $vendorId = $ccda->vendor_id;

                $allergies = AllergyImport::whereCcdaId($id)->whereSubstituteId(null)->get();
                $demographics = DemographicsImport::whereCcdaId($id)->whereSubstituteId(null)->first();
                $medications = MedicationImport::whereCcdaId($id)->whereSubstituteId(null)->get();
                $problems = ProblemImport::whereCcdaId($id)->whereSubstituteId(null)->get();

                $strategies = empty($ccda->vendor_id)
                    ?: CcdVendor::find($ccda->vendor_id)->routine()->first()->strategies()->get();

                if ($ccda->qaSummary->duplicate_id) {
                    $user = User::find($ccda->qaSummary->duplicate_id);

                    $user->ccdAllergies()->delete();
                    $user->ccdInsurancePolicies()->delete();
                    $user->ccdMedications()->delete();
                    $user->ccdProblems()->delete();
                    $user->patientCareTeamMembers()->delete();
                    $user->cpmBloodPressure()->delete();
                    $user->cpmBloodSugar()->delete();
                    $user->cpmSmoking()->delete();
                    $user->cpmWeight()->delete();

                    $user->cpmProblems()->detach();
                    $user->cpmBiometrics()->detach();
                    $user->cpmLifestyles()->detach();
                    $user->cpmMedicationGroups()->detach();
                    $user->cpmMiscs()->detach();
                    $user->cpmSymptoms()->detach();
                } else {
                    $user = $this->repo->createRandomUser($demographics);
                }

                $importer = new ImportManager($allergies->all(), $demographics, $medications->all(), $problems->all(), $strategies->all(), $user, $ccda);
                $importer->import();

                $imported[] = [
                    'ccdaId' => $id,
                    'userId' => $user->id,
                ];

                $ccda->imported = true;
                $ccda->patient_id = $user->id;
                $ccda->save();

                $allergiesDelete = AllergyImport::whereCcdaId($id)->delete();
                $demographicsDelete = $demographics->delete();
                $medicationsDelete = MedicationImport::whereCcdaId($id)->delete();
                $problemsDelete = ProblemImport::whereCcdaId($id)->delete();

                $ccda->qaSummary()->delete();
            }
        }


        if (!empty($ccdsToDelete)) {
            $deleted = [];

            foreach ($ccdsToDelete as $id) {
                if (empty($id)) continue;
                Ccda::destroy($id);
                $summary = QAImportSummary::whereCcdaId($id)->first();
                if (!empty($summary)) $summary->delete();

                $deleted[] = $id;
            }
        }

        return response()->json(compact('imported', 'deleted'), 200);
    }

}
