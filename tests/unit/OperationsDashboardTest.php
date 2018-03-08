<?php

namespace Tests\Unit;

use App\Services\OperationsDashboardService;
use App\User;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OperationsDashboardTest extends TestCase
{
    private $service;
    private $date;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_it_gets_patients()
    {
        $toDate = $this->date->endOfMonth();

        $patients = $this->service->getTotalPatients($this->date, $toDate);

        //not retrieving patients still TODO
        $this->assertNotNull($patients);
    }

    public function test_it_counts_patients_by_status(){

        $patients = User::with('patientInfo')->where('id', '<', 1000)->whereHas('patientinfo', function ($p){$p->where('ccm_status', 'paused');})->get();
        $counts = $this->service->countPatientsByStatus($patients);

        $this->assertNotNull($counts);
        $this->assertArrayHasKey('pausedPatients', $counts);

    }

    public function setUp()
    {
        parent::setUp();

        $this->service = new OperationsDashboardService();
        $this->date = Carbon::now()->subMonth();
    }
}
