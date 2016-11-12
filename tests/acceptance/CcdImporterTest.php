<?php

use Modelizer\Selenium\SeleniumTestCase;
use Tests\Helpers\HandlesUsersAndCarePlans;

class CcdImporterTest extends SeleniumTestCase
{
    use HandlesUsersAndCarePlans;

    protected $admin;

    public function setUp()
    {
        parent::setUp();

        config(['app.debug' => false]);

        $this->admin = $this->createUser(9, 'administrator');
    }

    /**
     * Test Importing a CCDA from Aprima.
     *
     * @return void
     */
    public function testImportAprimaCcda()
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
            ->wait(5);
    }
}
