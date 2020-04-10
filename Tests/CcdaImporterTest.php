<?php

namespace CircleLinkHealth\Eligibility\Tests;

use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\AttachDefaultPatientContactWindows;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportAllergies;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportBillingProvider;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportInsurances;
use CircleLinkHealth\Eligibility\Tests\Fakers\FakeCalvaryCcda;
use Tests\CustomerTestCase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CcdaImporterTest extends CustomerTestCase
{
    public function test_it_imports_csv_ccda_allergies()
    {
        $ccda = FakeCalvaryCcda::create();
        
        ImportAllergies::for($this->patient(), $ccda);
        
        $allergies = $this->patient()->ccdAllergies()->get();
        
        $this->assertCount(1, $allergies);
        $this->assertTrue('macrodantin' === $allergies->first()->allergen_name);
    }
    
    public function test_it_imports_csv_ccda_billing_provider()
    {
        $ccda = FakeCalvaryCcda::create(['billing_provider_id' => $this->provider()->id]);
        
        ImportBillingProvider::for($this->patient(), $ccda);
        
        $this->assertTrue($this->provider()->id === $this->patient()->billingProviderUser()->id);
    }
    
    public function test_it_attaches_default_contact_windows()
    {
        $ccda = FakeCalvaryCcda::create();
        
        AttachDefaultPatientContactWindows::for($this->patient(), $ccda);
        
        $this->assertTrue(
            $this->patient()->patientInfo->contactWindows()->pluck('day_of_week')->all() === [1, 2, 3, 4, 5]
        );
    }
    
    public function test_it_imports_insurances()
    {
        $ccda = FakeCalvaryCcda::create();
        
        ImportInsurances::for($this->patient(), $ccda);
        
        $insurances = $this->patient()->ccdInsurancePolicies;
        
        $this->assertCount(2, $insurances);
        
        $this->assertDatabaseHas(
            'ccd_insurance_policies',
            [
                'name'       => 'MEDICARE Part A',
                'type'       => 'primary_insurance',
                'policy_id'  => null,
                'relation'   => null,
                'subscriber' => null,
                'approved'   => false,
            ]
        );
        
        $this->assertDatabaseHas(
            'ccd_insurance_policies',
            [
                'name'       => 'Test Secondary Insurance',
                'type'       => 'secondary_insurance',
                'policy_id'  => null,
                'relation'   => null,
                'subscriber' => null,
                'approved'   => false,
            ]
        );
        
    }
}
