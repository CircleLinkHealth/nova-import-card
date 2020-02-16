<?php

namespace Tests\Feature;

use App\UPG\UPGPdfCarePlan;
use App\UPG\ValueObjects\PdfCarePlan;
use Tests\TestCase;

class UpgPdfReadTest extends TestCase
{
    protected $testFileName = 'test_upg_careplan.pdf';


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

        $this->assertTrue(! empty($carePlan));

        $this->assertEquals('Barbara', $carePlan['first_name']);


    }
}
