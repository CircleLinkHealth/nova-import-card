<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\UPG\UPGPdfCarePlan;
use App\UPG\ValueObjects\PdfCarePlan;
use Carbon\Carbon;
use Tests\TestCase;

class UpgPdfReadTest extends TestCase
{
    protected $testFileName = 'files-for-demos/upg0506/upg0506-care-plan.pdf';

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_it_reads_upg_pdf_successfully()
    {
        $carePlan = (new UPGPdfCarePlan($this->testFileName))->read();

        $this->assertTrue(is_a($carePlan, PdfCarePlan::class));

        $carePlan = $carePlan->toArray();

        $this->assertNotEmpty($carePlan);

        $this->assertEquals('Barbara', $carePlan['demographics']['name']['given'][0]);
        $this->assertEquals('Zznigro', $carePlan['demographics']['name']['family']);
        $this->assertEquals('334417', $carePlan['demographics']['mrn_number']);
        $this->assertEquals('female', $carePlan['demographics']['gender']);
        $this->assertTrue(Carbon::parse('05/25/1945')->eq($carePlan['demographics']['dob']));

        $this->assertEquals('(718)274-5745', collect($carePlan['demographics']['phones'])->where('type', 'home')->first()['number']);
        $this->assertEquals('8012 BROOKLYN, NY 11209', $carePlan['demographics']['address']['street'][0]);

        $this->assertCount(4, $carePlan['problems']);
        $this->assertEquals($carePlan['is_g0506'], 'true');
    }
}
