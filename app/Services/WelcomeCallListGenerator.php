<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 15/01/2017
 * Time: 8:27 PM
 */

namespace App\Services;


use App\CLH\CCD\Importer\SnomedToCpmIcdMap;
use App\Enrollee;
use App\Models\CPM\CpmProblem;
use App\Practice;
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

    /**
     * Filter the list by Last Encounter
     *
     * @var bool
     */
    public $filterLastEncounter;

    /**
     * Filter the list by Insurance
     *
     * @var bool
     */
    public $filterInsurance;

    /**
     * Filter the list by the number of CCM problems. Eligible Patients need to have at least 2.
     *
     * @var bool
     */
    public $filterProblems;

    /**
     * Create PreEnrollees
     *
     * @var bool
     */
    public $createEnrollees;

    public function __construct(
        Collection $patientList,
        $filterLastEncounter = true,
        $filterInsurance = true,
        $filterProblems = true,
        $createEnrollees = true,
        Practice $practice = null
    ) {
        $this->patientList = $patientList;
        $this->ineligiblePatients = new Collection();

        $this->filterLastEncounter = $filterLastEncounter;
        $this->filterInsurance = $filterInsurance;
        $this->filterProblems = $filterProblems;
        $this->createEnrollees = $createEnrollees;
        $this->practice = $practice;

        $this->filterPatientList();

        $this->createEnrollees();
    }

    protected function filterPatientList()
    {
        $this->byLastEncounter()
            ->byInsurance()
            ->byNumberOfProblems();
    }

    protected function byNumberOfProblems(): WelcomeCallListGenerator
    {
        if (!$this->filterProblems) {
            return $this;
        }
        $cpmProblems = CpmProblem::all();

        $patientList = $this->patientList->map(function ($row) use (
            $cpmProblems
        ) {
            $row['ccm_condition_1'] = '';
            $row['ccm_condition_2'] = '';
            $row['cpm_problem_1'] = '';
            $row['cpm_problem_2'] = '';

            if (is_string($row['problems'])) {
                $problems = new Collection(explode(',', $row['problems']));
            } elseif (is_a($row['problems'], Collection::class)) {
                $problems = $row['problems'];
            } else {
                dd('Problems is not a string or collection.');
            }

            $qualifyingProblems = [];
            //the cpm_problem_id for qualifying problems
            $qualifyingProblemsCpmIdStack = [];

            foreach ($problems as $problemCode) {

                if (!$problemCode) {
                    continue;
                }

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

                //try snomed
                $problem = SnomedToCpmIcdMap::where('snomed_code', '=', $problemCode)
                    ->first();

                if ($problem && !in_array($problem->cpm_problem_id, $qualifyingProblemsCpmIdStack)) {
                    $qualifyingProblems[] = "{$problem->cpmProblem->name}, ICD10: $problemCode";
                    $qualifyingProblemsCpmIdStack[] = $problem->cpm_problem_id;
                    continue;
                }

                /*
                 * Try to match keywords
                 */
                foreach ($cpmProblems as $problem) {
                    $keywords = array_merge(explode(',', $problem->contains), [$problem->name]);

                    foreach ($keywords as $keyword) {
                        if (empty($keyword)) {
                            continue;
                        }

                        if (str_contains(strtolower($problemCode), strtolower($keyword))
                            && !in_array($problem->id, $qualifyingProblemsCpmIdStack)
                        ) {
                            $code = SnomedToCpmIcdMap::where('icd_9_code', '!=', '')
                                ->whereCpmProblemId($problem->id)
                                ->get()
                                ->sortByDesc('icd_9_avg_usage')
                                ->first();

                            if ($code) {
                                if ($code->icd_9_code) {
                                    $code = "ICD9: $code->icd_9_code";
                                }
                            }

                            if (!$code) {
                                $code = SnomedToCpmIcdMap::where('icd_10_code', '!=', '')
                                    ->whereCpmProblemId($problem->id)
                                    ->first();
                                $code = "ICD10: $code->icd_10_code";
                            }

                            $qualifyingProblems[] = "{$problem->name}, $code";
                            $qualifyingProblemsCpmIdStack[] = $problem->id;
                        }
                    }
                }
            }

            $qualifyingProblems = array_unique($qualifyingProblems);

            if (count($qualifyingProblems) < 2) {
                $this->ineligiblePatients->push($row);

                return false;
            }

            $row['ccm_condition_1'] = $qualifyingProblems[0];
            $row['ccm_condition_2'] = $qualifyingProblems[1];

            $row['cpm_problem_1'] = $qualifyingProblemsCpmIdStack[0];
            $row['cpm_problem_2'] = $qualifyingProblemsCpmIdStack[1];

            return $row;
        })->values();

        $this->patientList = new Collection(array_filter($patientList->all()));

        return $this;
    }

    protected function byInsurance(): WelcomeCallListGenerator
    {
        if (!$this->filterInsurance) {
            return $this;
        }

        $this->patientList = $this->patientList->reject(function ($row) {
            $primary = strtolower($row['primary_insurance'] ?? null);
            $secondary = strtolower($row['secondary_insurance']  ?? null);

            //Change none to an empty string
            if (str_contains($primary, 'none')) {
                $primary = '';
            }
            if (str_contains($secondary, ['none', 'no secondary plan'])) {
                $primary = '';
            }

            //Keep the patient if they have medicaid
//            if (str_contains($primary, 'medicaid') || str_contains($secondary, 'medicaid')) {
//                return false;
//            }

            //Keep the patient if they have medicare AND a secondary insurance
            if (str_contains($primary, [
                    'medicare b',
                    'medicare part b',
                    'medicare',
                ]) && !empty($secondary)
            ) {
                return false;
            }

            //Or the reverse
            if (str_contains($secondary, [
                    'medicare b',
                    'medicare part b',
                    'medicare',
                ]) && !empty($primary)
            ) {
                return false;
            }

            //Otherwise, remove the patient from the list
            $this->ineligiblePatients->push($row);

            return true;

        });

        return $this;
    }

    /**
     * Removes Patients whose last encounter was before Feb. 1st, 2016 from the list.
     *
     * @return WelcomeCallListGenerator
     */
    protected function byLastEncounter(): WelcomeCallListGenerator
    {
        if (!$this->filterLastEncounter) {
            return $this;
        }

        $this->patientList = $this->patientList->reject(function ($row) {
            //Anything past this date is valid
            $minEligibleDate = Carbon::createFromDate('2016', '02', '01');

            if (!isset($row['last_encounter'])) {
                $this->ineligiblePatients->push($row);

                return true;
            }

            if (!$row['last_encounter']) {
                $this->ineligiblePatients->push($row);

                return true;
            }

            $lastEncounterDate = new Carbon($row['last_encounter']);

            if ($lastEncounterDate->lt($minEligibleDate)) {
                $this->ineligiblePatients->push($row);

                return true;
            }

            return false;
        });

        return $this;
    }

    /**
     * Create PreEnrollees from the filtered patientList
     *
     * @return $this
     */
    protected function createEnrollees()
    {
        if (!$this->createEnrollees) {
            return $this;
        }

        foreach ($this->patientList as $patient) {
            $args = $patient;

//            $args['status'] = Enrollee::TO_CALL;
//
//            if (isset($args['cell_phone'])) {
//                $args['status'] = Enrollee::TO_SMS;
//            }

            $args['practice_id'] = $this->practice->id;
            $args['provider_id'] = $this->practice->user_id;

            if (is_a($args, Collection::class)) {
                $args = $args->all();
            }

            if (empty($args['email'])) {
                $args['email'] = 'noEmail@noEmail.com';
            }

            $args['address'] = $args['street'];
            $args['address_2'] = $args['street2'] ?? '';

            $this->enrollees = Enrollee::updateOrCreate([
                'mrn' => $args['mrn'] ?? $args['mrn_number'],
            ], $args);
        }
    }

    /**
     * Exports the Patient List to a csv file.
     */
    public function exportToCsv()
    {
        $now = Carbon::now()->toDateTimeString();

        $this->patientList = $this->patientList->map(function ($patient) {
            $requiredKeys = [
                'patient_id',
                'email',
                'first_name',
                'last_name',
                'home_phone',
                'primary_phone',
                'work_phone',
                'preferred_contact_method',
                'preferred_provider',
                'address_1',
                'address_2',
                'city',
                'state',
                'zip',
                'patient_name',
                'last_encounter',
                'allergy',
                'primary_insurance',
                'secondary_insurance',
                'provider',
                'county',
                'medications',
                'problems',
                'ccm_condition_1',
                'ccm_condition_2',
                'cpm_problem_1',
                'cpm_problem_2',
            ];

            $keys = $patient->keys();

            foreach ($requiredKeys as $k) {
                if (!$keys->contains($k)) {
                    $patient->put($k, '');
                }
            }

            $patientArr = $patient->all();

            ksort($patientArr);

            return $patientArr;
        });

        return Excel::create("Welcome Call List - $now", function ($excel) {
            $excel->sheet('Welcome Calls', function ($sheet) {
                $sheet->fromArray(
                    $this->patientList->values()->all()
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

        $this->ineligiblePatients = $this->ineligiblePatients->map(function ($patient) {
            $requiredKeys = [
                'patient_id',
                'email',
                'first_name',
                'last_name',
                'home_phone',
                'primary_phone',
                'work_phone',
                'preferred_contact_method',
                'preferred_provider',
                'address_1',
                'address_2',
                'city',
                'state',
                'zip',
                'patient_name',
                'last_encounter',
                'allergy',
                'primary_insurance',
                'secondary_insurance',
                'provider',
                'county',
                'medications',
                'problems',
                'ccm_condition_1',
                'ccm_condition_2',
                'cpm_problem_1',
                'cpm_problem_2',
            ];

            $keys = $patient->keys();

            foreach ($requiredKeys as $k) {
                if (!$keys->contains($k)) {
                    $patient->put($k, '');
                }
            }

            $patientArr = $patient->all();

            ksort($patientArr);

            return $patientArr;
        });

        Excel::create("Ineligible Patients Welcome Call List - $now", function ($excel) {
            $excel->sheet('Ineligible', function ($sheet) {
                $sheet->fromArray(
                    $this->ineligiblePatients->values()->all()
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