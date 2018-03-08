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
    public function test_it_gets_and_counts_patients()
    {
        $fromDate = $this->date->startOfMonth()->toDateString();
        $toDate = $this->date->endOfMonth()->toDateString();

        $cpmTotalsDay = $this->service->getCpmPatientTotals($this->date, 'day');

        $this->assertNotNull($cpmTotalsDay);

        $monthPatients = $this->service->getTotalPatients($fromDate, $toDate);

        $this->assertNotNull($monthPatients);

        $counts = $this->service->countPatientsByStatus($monthPatients);

        $this->assertNotNull($counts);
        $this->assertArrayHasKey('pausedPatients', $counts);

        $filteredByPractice = $this->service->filterPatientsByPractice($monthPatients, 8);

        $this->assertNotNull($filteredByPractice);

        $pausedPatients = $this->service->getPausedPatients($fromDate, $toDate);

        $this->assertNotNull($pausedPatients);

        $allPatients = $this->service->getTotalPatients();

        $this->assertNotNull($allPatients);


    }


    public function setUp()
    {
        parent::setUp();

        $this->service = new OperationsDashboardService();
        $this->date = Carbon::now()->subMonth(2);
    }
}
