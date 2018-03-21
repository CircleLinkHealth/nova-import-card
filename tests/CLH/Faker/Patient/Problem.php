<?php


namespace CLH\Faker\Patient;


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

                $problem = $ccdProblem->merge($problemCodes);

            } else {
                $ccdProblem = $this->ccdProblems->random();
                $problemCodes = $this->problemCodes->where('problem_id', $ccdProblem->id)->all();
                $problem = $ccdProblem->merge($problemCodes);
            }

            return $problem;

        } else {
            if ($name) {
                $cpmProblem = $this->cpmProblems->firstWhere('name', $name);
                $problem    = $this->ccdProblems->firstWhere('cpm_problem_id', $cpmProblem->id);

            } else {
                $problem = $this->ccdProblems->random();
            }

            return $problem;

        }


    }


    /**
     * returns array of `ccd_problem` for sample patients
     *
     * @param bool $withCodes
     */
    public function problemSet($withCodes = true)
    {

    }

    /**
     *
     * attaches problems to user
     *
     * @param User $patient
     */
    public function attachProblemSet(User $patient)
    {

        //Search for attach problem on User model

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