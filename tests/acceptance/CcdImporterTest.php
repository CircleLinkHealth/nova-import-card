<?php

use App\Models\CCD\CcdVendor;
use Modelizer\Selenium\SeleniumTestCase;
use Tests\Helpers\CarePlanHelpers;
use Tests\Helpers\CcdImporterHelpers;
use Tests\Helpers\UserHelpers;

class CcdImporterTest extends SeleniumTestCase
{
    use CcdImporterHelpers,
        CarePlanHelpers,
        UserHelpers;

    protected $admin;

    public function setUp()
    {
        parent::setUp();

        $this->admin = $this->createUser(9, 'administrator');
    }

    /**
     * Test QAImporting a CCDA from Mazhar (Athena EHR).
     *
     * @return void
     */
    public function test_it_imports_mazhar_ccda()
    {
        $this->qaImport(
            storage_path('/ccdas/Samples/mazhar-sample.xml'),
            CcdVendor::whereVendorName('Mazhar')->first()
        );
    }

    /**
     * Test QAImporting a CCDA from UPG (Aprima EHR).
     *
     * @return void
     */
    public function test_it_imports_upg_ccda()
    {
        $this->qaImport(storage_path('/ccdas/Samples/upg-sample.xml'), CcdVendor::whereVendorName('UPG')->first());
    }

    /**
     * Test QAImporting a CCDA from Tabernacle (STI EHR).
     *
     * @return void
     */
    public function test_it_imports_tabernacle_ccda()
    {
        $this->qaImport(
            storage_path('/ccdas/Samples/tabernacle-sample.xml'),
            CcdVendor::whereVendorName('Tabernacle')->first()
        );
    }
}
