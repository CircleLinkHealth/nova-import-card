<?php

namespace Tests\Feature;

use App\OCR\UPGPdfCarePlan;
use Tests\TestCase;

class OcrTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        $string = (new UPGPdfCarePlan('test2.pdf'))->read();

        $this->assertNotNull($string);
    }
}
