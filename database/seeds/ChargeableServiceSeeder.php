<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Customer\Entities\ChargeableService;
use Illuminate\Database\Seeder;

class ChargeableServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        ChargeableService::updateOrCreate([
            'code' => 'CPT 99490',
        ], [
            'description' => 'CCM Services over 20 mins (1 month).',
            'amount'      => null,
        ]);

        ChargeableService::updateOrCreate([
            'code' => 'CPT 99484',
        ], [
            'description' => 'Behavioural Health Services over 20 mins (1 month)',
            'amount'      => null,
        ]);

        ChargeableService::updateOrCreate([
            'code' => 'G0506',
        ], [
            'description' => 'Enrollment in office & Care Planning by Provider',
            'amount'      => null,
        ]);

        ChargeableService::updateOrCreate([
            'code' => 'G0511',
        ], [
            'description' => 'FQHC / RHC General Care Management (1 month)',
            'amount'      => null,
        ]);

        ChargeableService::updateOrCreate([
            'code' => 'Software-Only',
        ], [
            'description' => 'Customer uses their own Care Center.',
            'amount'      => null,
        ]);
    }
}
