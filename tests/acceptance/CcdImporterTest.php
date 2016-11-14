<?php

use App\Models\CCD\QAImportSummary;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modelizer\Selenium\SeleniumTestCase;
use Tests\Helpers\CarePlanHelpers;
use Tests\Helpers\UserHelpers;

class CcdImporterTest extends SeleniumTestCase
{
    use DatabaseTransactions,
        CarePlanHelpers,
        UserHelpers;


    protected $admin;

    public function setUp()
    {
        parent::setUp();

        $this->admin = $this->createUser(9, 'administrator');
    }

    /**
     * Test QAImporting a CCDA from Aprima.
     *
     * @return void
     */
    public function testQAImportAprimaCcda()
    {
        $this->actingAs($this->admin)
            ->visit(route('patients.dashboard'))
            ->click('Import CCDs')
            ->seePageIs('/ccd-importer/create')
            ->findElement('label-vendor-1')
            ->click();

        $this
            ->type(storage_path('/ccdas/Samples/aprima-sample.xml'), 'ccd')
            ->press('Upload')
            ->seePageIs('/ccd-importer/qaimport')
            ->see('Name')
            ->see('Provider')
            ->see('Has Phone')
            ->see('Import')
            ->see('Delete')
            ->see('IMPORT/DELETE CHECKED CCDS');

        $summary = QAImportSummary::all()->last();

        $this->assertTrue(!empty($summary->name));
        $this->see($summary->name);

        $this->assertGreaterThan(0, $summary->medications);
        $this->assertGreaterThan(0, $summary->problems);

        $this->assertTrue(boolval($summary->has_phone));
    }
}
