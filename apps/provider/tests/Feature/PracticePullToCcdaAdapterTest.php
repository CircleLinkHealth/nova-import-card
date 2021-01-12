<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Models\PracticePull\Demographics;
use CircleLinkHealth\Core\DirectMail\Adapters\Ccda\PracticePullMedicalRecordToXmlAdapter;
use CircleLinkHealth\Customer\Tests\CustomerTestCase;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\PracticePullMedicalRecord;
use CircleLinkHealth\SharedModels\Entities\Ccda;

class PracticePullToCcdaAdapterTest extends CustomerTestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_it_creates_ccda_from_practice_pull_data()
    {
        $x = new PracticePullMedicalRecordToXmlAdapter($this->practicePullCcda());
        $x->createAndStoreXml();
    }

    private function practicePullCcda()
    {
        $demos = factory(Demographics::class)->create([
            'location_id'     => $this->location()->id,
            'practice_id'     => $this->practice()->id,
            'patient_user_id' => $this->patient()->id,
        ]);
        $mr = new PracticePullMedicalRecord($demos->mrn, $this->practice()->id);

        return new Ccda([
            'location_id' => $this->location()->id,
            'practice_id' => $this->practice()->id,
            'patient_id'  => $this->patient()->id,
            'json'        => $mr->toJson(),
        ]);
    }
}
