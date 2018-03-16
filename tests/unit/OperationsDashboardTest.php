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
        //date for month
        $fromDate = $this->date->startOfMonth()->toDateString();
        $toDate = $this->date->endOfMonth()->toDateString();

        //tests getting all patients (paused, withdrawn, enrolled and those that have careplan with status to enroll)
        $allPatients = $this->service->getTotalPatients();
        $this->assertNotNull($allPatients);

        //get data for Total Patients table when selecting date from 'select day'
        //also tests for methods 'getTotalPatients' and 'countPatientsByStatus'
        $cpmTotalsDay = $this->service->getCpmPatientTotals($this->date, 'day');
        $this->assertNotNull($cpmTotalsDay);

        //tests method for 2 given dates
        $monthPatients = $this->service->getTotalPatients($fromDate, $toDate);
        $this->assertNotNull($monthPatients);

        //tests count method
        $counts = $this->service->countPatientsByStatus($monthPatients);
        $this->assertNotNull($counts);
        $this->assertArrayHasKey('pausedPatients', $counts);

        //tests filtering result by practice, countPatientsByStatus, returns collection of counts
        $filteredByPractice = $this->service->filterPatientsByPractice($monthPatients, 188);
        $this->assertNotNull($filteredByPractice);


        //gets all paused patients for given dates, always takes 2 dates
        $pausedPatients = $this->service->getPausedPatients($fromDate, $toDate);
        $this->assertNotNull($pausedPatients);



    }


    public function setUp()
    {
        parent::setUp();

        $this->service = new OperationsDashboardService();
        $this->date = Carbon::now()->subMonth(2);
    }
}
