<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Adapters;

class JsonMedicalRecordInsurancePlansAdapter
{
    /**
     * Adapt Insurance Plans from `CLH Medical Record Template`
     * https://gist.github.com/michalisantoniou6/853740eff3ed58814a89d12c922840c3 to primary, secondary and tertiary
     * insurance.
     *
     * @return array
     */
    public function adapt(array $record)
    {
        collect($record['insurance_plans'] ?? $record['insurance_plan'])
            ->each(function ($plan, $key) use (&$record) {
                $concatString = null;

                $planName = $plan['plan'] ?? '';
                $groupNumber = $plan['group_number'] ?? '';
                $policyNumber = $plan['policy_number'] ?? '';
                $insuranceType = $plan['insurance_type'] ?? '';

                if ($planName || $groupNumber || $policyNumber || $insuranceType) {
                    $concatString = "{$planName} - {$groupNumber} - {$policyNumber} - {$insuranceType}";
                }

                if ('primary' == $key) {
                    $record['primary_insurance'] = $concatString;
                } elseif ('secondary' == $key) {
                    $record['secondary_insurance'] = $concatString;
                } elseif ('tertiary' == $key) {
                    $record['tertiary_insurance'] = $concatString;
                }
            });

        return $record;
    }
}
