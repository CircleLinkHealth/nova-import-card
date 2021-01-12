<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SharedModels\Entities\CpmBiometric;
use Illuminate\Database\Seeder;

class CpmBiometricSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CpmBiometric::where(['id' => 1])->update(['unit' => 'lbs']);
        CpmBiometric::where(['id' => 2])->update(['unit' => 'mm Hg']);
        CpmBiometric::where(['id' => 3])->update(['unit' => 'mg/dL']);
        CpmBiometric::where(['id' => 4])->update(['unit' => '# per day']);
    }
}
