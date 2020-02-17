<?php

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

        $this->assertTrue(is_json($carePlan->toJson()));

        $carePlan = $carePlan->toArray();

        $this->assertNotEmpty($carePlan);

        $this->assertEquals('Barbara', $carePlan['first_name']);
        $this->assertEquals('Zznigro', $carePlan['last_name']);
        $this->assertEquals('334417', $carePlan['mrn']);
        $this->assertEquals('female', $carePlan['sex']);
        $this->assertTrue(Carbon::parse('01/29/2020')->eq($carePlan['visit_date']));
        $this->assertTrue(Carbon::parse('05/25/1945')->eq($carePlan['dob']));


        $this->assertEquals('(718)274-5745', collect($carePlan['phones'])->where('type', 'home_phone')->first()['value']);
        $this->assertEquals('8012 BROOKLYN, NY 11209', $carePlan['address']);

        $this->assertCount(4, $carePlan['problems']);

        $this->assertEquals('Jeffrey', $carePlan['provider']['first_name']);
        $this->assertEquals('Hyman', $carePlan['provider']['last_name']);

        $this->assertTrue($carePlan['chargeable_services'][0]['is_g0506']);
    }
}
