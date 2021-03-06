<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SharedModels\Entities\Note;
use Illuminate\Database\Seeder;

class RemoveBiometricNotes extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        return Note::where(['type' => 'Biometrics'])->delete();
    }
}
