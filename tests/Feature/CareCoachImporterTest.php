<?php

namespace Tests\Feature;

use App\Practice;
use Tests\Helpers\UserHelpers;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CareCoachImporterTest extends TestCase
{
    use UserHelpers;
    
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_care_coach_can_see_importer_page()
    {
        $practice = Practice::first();
        $careCoach = $this->createUser($practice->id, 'care-center');
        
        $this->assertTrue($careCoach->hasPermission('ccd-import'));
    }
}
