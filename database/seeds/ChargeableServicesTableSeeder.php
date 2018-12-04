<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Seeder;

class ChargeableServicesTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     */
    public function run()
    {
        \DB::table('chargeable_services')->delete();

        \DB::table('chargeable_services')->insert([
            0 => [
                'id'          => 1,
                'code'        => 'CPT 99490',
                'description' => 'CCM Services over 20 mins (1 month).',
                'amount'      => null,
                'created_at'  => '2018-02-05 08:33:55',
                'updated_at'  => '2018-02-05 08:38:34',
            ],
            1 => [
                'id'          => 2,
                'code'        => 'CPT 99487',
                'description' => 'Complex CCM over 60 mins (1 month)',
                'amount'      => '70.00',
                'created_at'  => '2018-02-24 17:13:02',
                'updated_at'  => '2018-02-24 17:13:02',
            ],
            2 => [
                'id'          => 3,
                'code'        => 'CPT 99489',
                'description' => 'Complex CCM additional 30 mins (1 month)',
                'amount'      => '35.00',
                'created_at'  => '2018-02-24 17:13:02',
                'updated_at'  => '2018-02-24 17:13:02',
            ],
            3 => [
                'id'          => 4,
                'code'        => 'CPT 99484',
                'description' => 'Behavioural Health Services over 20 mins (1 month)',
                'amount'      => '33.00',
                'created_at'  => '2018-02-24 17:13:02',
                'updated_at'  => '2018-02-24 17:13:02',
            ],
            4 => [
                'id'          => 5,
                'code'        => 'G0506',
                'description' => 'Enrollment in office & Care Planning by Provider',
                'amount'      => '9.99',
                'created_at'  => '2018-02-24 17:13:02',
                'updated_at'  => '2018-02-24 17:13:02',
            ],
            5 => [
                'id'          => 6,
                'code'        => 'G0511',
                'description' => 'FQHC / RHC General Care Management (1 month)',
                'amount'      => '41.00',
                'created_at'  => '2018-02-24 17:13:02',
                'updated_at'  => '2018-02-24 17:13:02',
            ],
        ]);
    }
}
