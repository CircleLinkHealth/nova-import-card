<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class SnomedToIcd9TestMapTableSeeder extends Seeder
{
    public function items(): Collection
    {
        $items = [
            0 => [
                'id'           => 1,
                'ccm_eligible' => 0,
                'code'         => 'V76.12',
                'name'         => 'Other screening mammogram',
                'avg_usage'    => 1.4437800000000001,
                'is_nec'       => 1,
                'snomed_code'  => 0,
                'snomed_name'  => '',
            ],
            1 => [
                'id'           => 2,
                'ccm_eligible' => 0,
                'code'         => '250',
                'name'         => 'Diabetes mellitus without mention of complication, type II or unspecified type, not stated as uncontrolled',
                'avg_usage'    => 1.3794500000000001,
                'is_nec'       => 0,
                'snomed_code'  => 44054006,
                'snomed_name'  => 'Diabetes mellitus type 2 (disorder)',
            ],
            2 => [
                'id'           => 3,
                'ccm_eligible' => 0,
                'code'         => '250',
                'name'         => 'Diabetes mellitus without mention of complication, type II or unspecified type, not stated as uncontrolled',
                'avg_usage'    => 1.3794500000000001,
                'is_nec'       => 0,
                'snomed_code'  => 73211009,
                'snomed_name'  => 'Diabetes mellitus (disorder)',
            ],
            3 => [
                'id'           => 4,
                'ccm_eligible' => 0,
                'code'         => '250',
                'name'         => 'Diabetes mellitus without mention of complication, type II or unspecified type, not stated as uncontrolled',
                'avg_usage'    => 1.3794500000000001,
                'is_nec'       => 0,
                'snomed_code'  => 313436004,
                'snomed_name'  => 'Type II diabetes mellitus without complication (disorder)',
            ],
            4 => [
                'id'           => 5,
                'ccm_eligible' => 0,
                'code'         => '250',
                'name'         => 'Diabetes mellitus without mention of complication, type II or unspecified type, not stated as uncontrolled',
                'avg_usage'    => 1.3794500000000001,
                'is_nec'       => 0,
                'snomed_code'  => 11530004,
                'snomed_name'  => 'Brittle diabetes mellitus (finding)',
            ],
            5 => [
                'id'           => 6,
                'ccm_eligible' => 0,
                'code'         => '250',
                'name'         => 'Diabetes mellitus without mention of complication, type II or unspecified type, not stated as uncontrolled',
                'avg_usage'    => 1.3794500000000001,
                'is_nec'       => 0,
                'snomed_code'  => 237599002,
                'snomed_name'  => 'Insulin treated type 2 diabetes mellitus (disorder)',
            ],
            6 => [
                'id'           => 7,
                'ccm_eligible' => 0,
                'code'         => '250',
                'name'         => 'Diabetes mellitus without mention of complication, type II or unspecified type, not stated as uncontrolled',
                'avg_usage'    => 1.3794500000000001,
                'is_nec'       => 0,
                'snomed_code'  => 81531005,
                'snomed_name'  => 'Diabetes mellitus type 2 in obese (disorder)',
            ],
            7 => [
                'id'           => 8,
                'ccm_eligible' => 0,
                'code'         => '250',
                'name'         => 'Diabetes mellitus without mention of complication, type II or unspecified type, not stated as uncontrolled',
                'avg_usage'    => 1.3794500000000001,
                'is_nec'       => 0,
                'snomed_code'  => 199230006,
                'snomed_name'  => 'Pre-existing type 2 diabetes mellitus (disorder)',
            ],
            8 => [
                'id'           => 9,
                'ccm_eligible' => 0,
                'code'         => '250',
                'name'         => 'Diabetes mellitus without mention of complication, type II or unspecified type, not stated as uncontrolled',
                'avg_usage'    => 1.3794500000000001,
                'is_nec'       => 0,
                'snomed_code'  => 609572000,
                'snomed_name'  => 'Maturity-onset diabetes of the young, type 5 (disorder)',
            ],
            9 => [
                'id'           => 10,
                'ccm_eligible' => 0,
                'code'         => '250',
                'name'         => 'Diabetes mellitus without mention of complication, type II or unspecified type, not stated as uncontrolled',
                'avg_usage'    => 1.3794500000000001,
                'is_nec'       => 0,
                'snomed_code'  => 609573005,
                'snomed_name'  => 'Maturity-onset diabetes of the young, type 6 (disorder)',
            ],
            10 => [
                'id'           => 11,
                'ccm_eligible' => 0,
                'code'         => '250',
                'name'         => 'Diabetes mellitus without mention of complication, type II or unspecified type, not stated as uncontrolled',
                'avg_usage'    => 1.3794500000000001,
                'is_nec'       => 0,
                'snomed_code'  => 609574004,
                'snomed_name'  => 'Maturity-onset diabetes of the young, type 7 (disorder)',
            ],
        ];

        return new Collection($items);
    }

    /**
     * Auto generated seed file.
     */
    public function run()
    {
        \DB::table('snomed_to_icd9_map')->delete();

        ini_set('memory_limit', '512M'); // remove memory limit

        $this->items()->map(function ($item) {
            \DB::table('snomed_to_icd9_map')->insert([$item]);

            $name = $item['name'];

            $this->command->info("${name} seeded");
        });
    }
}
