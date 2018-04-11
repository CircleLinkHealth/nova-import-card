<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 12/26/2017
 * Time: 3:15 PM
 */

namespace App\Adapters\EligibilityCheck;


use App\Services\WelcomeCallListGenerator;
use App\ValueObjects\Athena\ProblemsAndInsurances;

class AthenaAPIAdapter
{
    private $problemsAndInsurances;
    private $eligiblePatientList;

    public function __construct(ProblemsAndInsurances $problemsAndInsurances)
    {
        $this->problemsAndInsurances = $problemsAndInsurances;
    }

    public function isEligible() {
        $patientList = collect();

        $patient = collect([
            'problems' => $this->problemsAndInsurances->getProblemCodes(),
            'insurances' => $this->problemsAndInsurances->getInsurancesForEligibilityCheck(),
        ]);

        $patientList->push($patient);

        $check = new WelcomeCallListGenerator($patientList, false, true, true, false);

        $this->eligiblePatientList = $check->getPatientList();

        return $this->eligiblePatientList->count() > 0;
    }

    /**
     * @return mixed
     */
    public function getEligiblePatientList()
    {
        return $this->eligiblePatientList;
    }
}