<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 8/21/19
 * Time: 3:06 PM
 */

namespace App\Contracts;


use App\EligibilityJob;

/**
 * Medical Records which can be checked for Eligibility can implement this contract
 *
 * Interface EligibilityCheckable
 * @package App\Contracts
 */
interface EligibilityCheckable
{
    /**
     * @return \App\EligibilityJob
     */
    public function createEligibilityJobFromMedicalRecord(): EligibilityJob;
}