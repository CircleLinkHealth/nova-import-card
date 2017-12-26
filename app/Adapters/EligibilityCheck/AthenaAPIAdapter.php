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

    public function __construct(ProblemsAndInsurances $problemsAndInsurances)
    {
        $this->problemsAndInsurances = $problemsAndInsurances;
    }

    public function isEligible() {
        $patient = collect([
            'problems' => $this->problemsAndInsurances->getProblems(),
            'primary_insurance' => '',
            'secondary_insurance' => '',
        ]);

        $check = new WelcomeCallListGenerator($patient, false, true, true, false);

        return $check->getPatientList()->count() > 0;
    }
}