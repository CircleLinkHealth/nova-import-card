<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 15/01/2017
 * Time: 8:27 PM
 */

namespace App\Services;


use App\CLH\CCD\Importer\SnomedToCpmIcdMap;
use App\Models\CPM\CpmProblem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class WelcomeCallListGenerator
{
    /**
     * An array representation of the Patient List. This will normally be uploaded as a csv file.
     *
     * @var Collection
     */
    public $patientList;

    /**
     * An array representation of the Ineligible. This will normally be uploaded as a csv file.
     *
     * @var Collection
     */
    public $ineligiblePatients;

    public function __construct(Collection $patientList)
    {
        $this->patientList = $patientList;

        $this->filterPatientList();
    }

    protected function filterPatientList()
    {
        $this->byLastEncounter()
            ->byInsurance()
            ->byNumberOfProblems();
    }

    protected function byNumberOfProblems() : WelcomeCallListGenerator
    {
        $cpmProblems = CpmProblem::all();

        $patientList = $this->patientList->map(function ($row) use
        (
            $cpmProblems
        ) {
            $row['ccm_condition_1'] = '';
            $row['ccm_condition_2'] = '';

            $problems = new Collection(explode(',', $row['problems']));

            $qualifyingProblems = [];
            //the cpm_problem_id for qualifying problems
            $qualifyingProblemsCpmIdStack = [];

            foreach ($problems as $problemCode) {

                $problemCode = trim($problemCode);

                //This was used for a list where problems where written as such: ICD-209: Diabetes,
                $from = strpos($problemCode, '-');
                $to = strpos($problemCode, ':');

                if ($from !== false && $to !== false) {
                    $problemCode = substr($problemCode, ++$from, $to - $from);
                }

                //try icd 9
                $problem = SnomedToCpmIcdMap::where('icd_9_code', '=', $problemCode)
                    ->first();

                if ($problem && !in_array($problem->cpm_problem_id, $qualifyingProblemsCpmIdStack)) {
                    $qualifyingProblems[] = "{$problem->cpmProblem->name}, ICD9: $problemCode";
                    $qualifyingProblemsCpmIdStack[] = $problem->cpm_problem_id;
                    continue;
                }

                //try icd 10
                $problem = SnomedToCpmIcdMap::where('icd_10_code', '=', $problemCode)
                    ->first();

                if ($problem && !in_array($problem->cpm_problem_id, $qualifyingProblemsCpmIdStack)) {
                    $qualifyingProblems[] = "{$problem->cpmProblem->name}, ICD10: $problemCode";
                    $qualifyingProblemsCpmIdStack[] = $problem->cpm_problem_id;
                    continue;
                }

                //Iffy ICD-9 test
                foreach ($cpmProblems as $problem) {
                    if ($problemCode >= $problem->icd9from
                        && $problemCode <= $problem->icd9to
                        && !in_array($problem->id, $qualifyingProblemsCpmIdStack)
                    ) {
                        $qualifyingProblems[] = "{$problem->name}, ICD9: $problemCode";
                        $qualifyingProblemsCpmIdStack[] = $problem->id;
                        continue 2;
                    }
                }
            }

            $qualifyingProblems = array_unique($qualifyingProblems);

            if (count($qualifyingProblems) < 2) {
                $this->ineligiblePatients[] = $row;
                return false;
            }

            $row['ccm_condition_1'] = $qualifyingProblems[0];
            $row['ccm_condition_2'] = $qualifyingProblems[1];

            return $row;
        })->values();

        $this->patientList = new Collection(array_filter($patientList->all()));

        return $this;
    }

    protected function byInsurance() : WelcomeCallListGenerator
    {
        $this->patientList = $this->patientList->reject(function ($row) {
            $primary = strtolower($row['primary_insurance'] ?? null);
            $secondary = strtolower($row['secondary_insurance']  ?? null);

            //Change none to an empty string
            if (str_contains($primary, 'none')) {
                $primary = '';
            }
            if (str_contains($secondary, 'none')) {
                $primary = '';
            }

            //Keep the patient if they have medicaid
//            if (str_contains($primary, 'medicaid') || str_contains($secondary, 'medicaid')) {
//                return false;
//            }

            //Keep the patient if they have medicate b AND a secondary insurance
            if (str_contains($primary, [
                    'medicare b',
                    'medicare part b',
                    'medicare',
                ]) && !empty($secondary)
            ) {
                return false;
            }

            //Otherwise, remove the patient from the list
//            $this->ineligiblePatients[] = $row;
            return true;

        });

        return $this;
    }

    /**
     * Removes Patients whose last encounter was before Feb. 1st, 2016 from the list.
     *
     * @return WelcomeCallListGenerator
     */
    protected function byLastEncounter() : WelcomeCallListGenerator
    {
        $this->patientList = $this->patientList->reject(function ($row) {
            //Anything past this date is valid
            $minEligibleDate = Carbon::createFromDate('2016', '02', '01');

            if (!isset($row['last_encounter'])) {
//                $this->ineligiblePatients[] = $row;
                return true;
            }

            if (!$row['last_encounter']) {
//                $this->ineligiblePatients[] = $row;
                return true;
            }

            $lastEncounterDate = new Carbon($row['last_encounter']);

            if ($lastEncounterDate->lt($minEligibleDate)) {
//                $this->ineligiblePatients[] = $row;
                return true;
            }

            return false;
        });

        return $this;
    }

    /**
     * Exports the Patient List to a csv file.
     */
    public function exportToCsv()
    {
        $now = Carbon::now()->toDateTimeString();

        return Excel::create("Welcome Call List - $now", function ($excel) {
            $excel->sheet('Welcome Calls', function ($sheet) {
                $sheet->fromArray(
                    $this->patientList
                );
            });
        })->export('xls');
    }

    /**
     * Exports the Ineligible Patient List to a csv file.
     */
    public function exportIneligibleToCsv()
    {
        $now = Carbon::now()->toDateTimeString();

        Excel::create("Ineligible Patients Welcome Call List - $now", function ($excel) {
            $excel->sheet('Ineligible', function ($sheet) {
                $sheet->fromArray(
                    $this->ineligiblePatients
                );
            });
        })->export('xls');
    }

    /**
     * @return Collection
     */
    public function getPatientList(): Collection
    {
        return $this->patientList;
    }
}