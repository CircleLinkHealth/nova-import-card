<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 10/12/18
 * Time: 1:23 PM
 */

namespace App\Services\Eligibility\Adapters;


class JsonMedicalRecordInsurancePlansAdapter
{
    /**
     * Adapt Insurance Plans from `CLH Medical Record Template`
     * https://gist.github.com/michalisantoniou6/853740eff3ed58814a89d12c922840c3 to primary, secondary and tertiary
     * insurance
     *
     * @param array $record
     *
     * @return array
     */
    public function adapt(array $record)
    {
        collect($record['insurance_plans'] ?? $record['insurance_plan'])
            ->each(function ($plan, $key) use (&$record) {
                $concatString = null;

                if ($plan['plan'] || $plan['group_number'] || $plan['policy_number'] || $plan['insurance_type']) {
                    $concatString = "{$plan['plan']} - {$plan['group_number']} - {$plan['policy_number']} - {$plan['insurance_type']}";
                }

                if ($key == 'primary') {
                    $record['primary_insurance'] = $concatString;
                } elseif ($key == 'secondary') {
                    $record['secondary_insurance'] = $concatString;
                } elseif ($key == 'tertiary') {
                    $record['tertiary_insurance'] = $concatString;
                }
            });

        return $record;
    }
}