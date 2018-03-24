<?php

namespace Tests\Unit;

use App\Repositories\OpsDashboardPatientEloquentRepository;
use App\Services\OpsDashboardService;
use App\User;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OpsDashboardTest extends TestCase
{
    private $service;
    private $repo;
    private $date;

    /**
     * Tests old dashboard
     *
     * @return void
     */
//    public function test_it_gets_and_counts_patients()
//    {
////        //date for month
//        //fix dates
////        $fromDate = $this->date->copy()->startOfMonth()->toDateString();
////        $toDate = $this->date->copy()->endOfMonth()->toDateString();
////
////        //tests getting all patients (paused, withdrawn, enrolled and those that have careplan with status to enroll)
////        $allPatients = $this->service->getTotalPatients();
////        $this->assertNotNull($allPatients);
////
////        //get data for Total Patients table when selecting date from 'select day'
////        //also tests for methods 'getTotalPatients' and 'countPatientsByStatus'
////        $cpmTotalsDay = $this->service->getCpmPatientTotals($this->date, 'day');
////        $this->assertNotNull($cpmTotalsDay);
////
////        //tests method for 2 given dates
////        $monthPatients = $this->service->getTotalPatients($fromDate, $toDate);
////        $this->assertNotNull($monthPatients);
////
////        //tests count method
////        $counts = $this->service->countPatientsByStatus($monthPatients);
////        $this->assertNotNull($counts);
////        $this->assertArrayHasKey('pausedPatients', $counts);
////
////        //tests filtering result by practice, countPatientsByStatus, returns collection of counts
////        $filteredByPractice = $this->service->filterPatientsByPractice($monthPatients, 188);
////        $this->assertNotNull($filteredByPractice);
////
////
////        //gets all paused patients for given dates, always takes 2 dates
////        $pausedPatients = $this->service->getPausedPatients($fromDate, $toDate);
////        $this->assertNotNull($pausedPatients);
//
//    }

    public function test_new_repository()
    {

        $fromDate = $this->date->copy()->subYear(2)->startOfYear()->startOfDay()->toDateTimeString();
        $toDate = $this->date->copy()->subYear(2)->endOfYear()->endOfDay()->toDateTimeString();

        $ccmPatients = $this->repo->getPatientsByCcmTime($fromDate, $toDate);
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $ccmPatients);
        $this->assertNotNull($ccmPatients);


        $totalPatients = $this->repo->getPatientsByStatus($fromDate, $toDate);
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $totalPatients);
        $this->assertNotNull($totalPatients);

        $enrolledPatients = $this->repo->getEnrolledPatients($fromDate, $toDate);
        $countsByCcmTime = $this->service->countPatientsByCcmTime($enrolledPatients, $fromDate, $toDate);

        $this->assertNotNull($countsByCcmTime);

    }


    public function setUp()
    {
        parent::setUp();

        $this->service = new OpsDashboardService();
        $this->repo = new OpsDashboardPatientEloquentRepository();
        $this->date = Carbon::now()->subMonth(2);
    }
}
