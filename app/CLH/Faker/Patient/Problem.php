<?php


namespace App\CLH\Faker\Patient;


use App\Models\CCD\Problem as CcdProblem;
use App\Models\CPM\CpmProblem;
use App\Models\ProblemCode;
use App\User;
use Illuminate\Database\Eloquent\Collection;

class Problem
{

    protected $ccdProblems;
    protected $cpmProblems;
    protected $problemCodes;


    /**
     * Problem constructor.
     */
    public function __construct()
    {
        $this->ccdProblems  = $this->getCcdProblems();
        $this->cpmProblems  = $this->getCpmProblems();
        $this->problemCodes = $this->getProblemCodes();

    }


    /**
     * returns `ccd_problem`
     *
     * @param bool $withCodes
     * @param null $name
     *
     * @return int
     */
    public function problem($withCodes = true, $name = null)
    {
        if ($withCodes == true) {
            if ($name) {
                $cpmProblem = $this->cpmProblems->firstWhere('name', $name);
                $ccdProblem    = $this->ccdProblems->firstWhere('cpm_problem_id', $cpmProblem->id);
                $problemCodes = $this->problemCodes->where('problem_id', $ccdProblem->id)->all();
                $ccdProblem->codes = $problemCodes;

            } else {
                $ccdProblem = $this->ccdProblems->random();
                $problemCodes = $this->problemCodes->where('problem_id', $ccdProblem->id)->all();

                $ccdProblem->codes = $problemCodes;
            }

            return $ccdProblem;

        } else {
            if ($name) {
                $cpmProblem = $this->cpmProblems->firstWhere('name', $name);
                $ccdProblem    = $this->ccdProblems->firstWhere('cpm_problem_id', $cpmProblem->id);

            } else {
                $ccdProblem = $this->ccdProblems->random();
            }

            return $ccdProblem;

        }


    }


    /**
     * returns array of `ccd_problem` for sample patients
     *
     * @param bool $withCodes
     *
     * @return array
     */
    public function problemSet($withCodes = true)
    {
        $patientIds = $this->ccdProblems->pluck('patient_id');
        $patientId = $patientIds->random();
        $problemSet = [];

        if ($withCodes){

            $ccdProblems = $this->ccdProblems->where('patient_id', $patientId)->all();
            foreach ($ccdProblems as $ccdProblem){
                $problemCodes = $this->problemCodes->where('problem_id', $ccdProblem->id)->all();
                if ($problemCodes){
                    $ccdProblem->codes = $problemCodes;
                    $problemSet[] = $ccdProblem;
                }else{
                    $problemSet[] = $ccdProblem;
                }

            }

            return $problemSet;

        }else{

            $problemSet[] = $this->ccdProblems->where('patient_id', $patientId)->all();
            return $problemSet;

        }

    }

    /**
     *
     * attaches problems to user
     *
     * @param User $patient
     */
    public function attachProblemSet(User $patient)
    {
        $problemSet = $this->problemSet();

//        $patient->ccdProblems = $problemSet;

        return $patient;


    }

    public function getCcdProblems(): Collection
    {

        $ccdProblems = CcdProblem::all();

        return $ccdProblems;
    }

    public function getCpmProblems(): Collection
    {

        $cpmProblems = CpmProblem::all();

        return $cpmProblems;
    }

    public function getProblemCodes(): Collection
    {

        $problemCodes = ProblemCode::all();

        return $problemCodes;
    }
}