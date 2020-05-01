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
            'order'       => 1,
            'is_enabled'  => true,
            'description' => 'CCM Services over 20 mins (1 month)',
            'amount'      => null,
        ]);

        ChargeableService::updateOrCreate([
            'code' => 'CPT 99484',
        ], [
            'order'       => 2,
            'is_enabled'  => true,
            'description' => 'Behavioural Health Services over 20 mins (1 month)',
            'amount'      => null,
        ]);

        ChargeableService::updateOrCreate([
            'code' => 'G2058(>40mins)',
        ], [
            'order'       => 3,
            'is_enabled'  => true,
            'description' => 'CCM services over 40 mins (1 month)',
            'amount'      => null,
        ]);

        ChargeableService::updateOrCreate([
            'code' => 'G2058(>60mins)',
        ], [
            'order'       => 4,
            'is_enabled'  => true,
            'description' => 'CCM services over 60 mins (1 month)',
            'amount'      => null,
        ]);

        ChargeableService::updateOrCreate([
            'code' => 'G0511',
        ], [
            'order'       => 5,
            'is_enabled'  => true,
            'description' => 'FQHC / RHC General Care Management (1 month)',
            'amount'      => null,
        ]);

        ChargeableService::updateOrCreate([
            'code' => 'Software-Only',
        ], [
            'order'       => 6,
            'is_enabled'  => true,
            'description' => 'Customer uses their own Care Center',
            'amount'      => null,
        ]);

        ChargeableService::updateOrCreate([
            'code' => 'AWV: G0438',
        ], [
            'order'       => 7,
            'is_enabled'  => true,
            'description' => 'Initial Annual Wellness Visit',
            'amount'      => null,
        ]);

        ChargeableService::updateOrCreate([
            'code' => 'AWV: G0439',
        ], [
            'order'       => 8,
            'is_enabled'  => true,
            'description' => 'Subsequent Annual Wellness Visit',
            'amount'      => null,
        ]);

        ChargeableService::updateOrCreate([
            'code' => 'CPT 99487',
        ], [
            'order'       => null,
            'is_enabled'  => false,
            'description' => 'Complex CCM over 60 mins (1 month)',
            'amount'      => null,
        ]);

        ChargeableService::updateOrCreate([
            'code' => 'CPT 99489',
        ], [
            'order'       => null,
            'is_enabled'  => false,
            'description' => 'Complex CCM additional 30 mins (1 month)',
            'amount'      => null,
        ]);

        ChargeableService::updateOrCreate([
            'code' => 'G0506',
        ], [
            'order'       => null,
            'is_enabled'  => false,
            'description' => 'Enrollment in office & Care Planning by Provider',
            'amount'      => null,
        ]);

        ChargeableService::updateOrCreate([
            'code' => 'G2065',
        ], [
            'order'       => 9,
            'is_enabled'  => true,
            'description' => 'PCM: Principal Care Management over 30 Minutes (1 month)',
            'amount'      => null,
        ]);
    }
}
