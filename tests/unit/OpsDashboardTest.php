<?php

namespace Tests\Unit;

use App\Http\Controllers\OpsDashboardController;
use App\PatientMonthlySummary;
use App\Practice;
use App\Repositories\OpsDashboardPatientEloquentRepository;
use App\Services\OpsDashboardService;
use App\User;
use Carbon\Carbon;
use Tests\Helpers\SetupTestCustomer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OpsDashboardTest extends TestCase
{
    use SetupTestCustomer,
        DatabaseTransactions;

    private $controller;
    private $service;
    private $repo;
    private $date;
    private $practice;
    private $location;
    private $provider;
    private $patient;
    private $total;



    public function test_billing_churn(){

        $months = 6;
        $fromDate = $this->date->copy()->subMonth($months)->startOfMonth()->startOfDay();
        $months   = $this->controller->getMonths($this->date, $months);

        $summaries = PatientMonthlySummary::with('patient')
                                          ->whereHas('patient')
                                          ->where('actor_id', '!=', null)
                                          ->where('approved', 1)
                                          ->where('month_year', '>=', $fromDate)
                                          ->get();

        $practices = Practice::activeBillable()->get()->sortBy('name');

        $rows = [];
        foreach ($practices as $practice) {
            $practiceSummaries             = $this->service->filterSummariesByPractice($summaries, $practice->id);
            $rows[$practice->display_name] = $this->service->billingChurnRow($practiceSummaries, $months);
        }
        $total = $this->controller->calculateBillingChurnTotalRow($rows, $months);
        $rows                     = collect($rows);


        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $rows);

    }


    public function setUp()
    {
        parent::setUp();

        $this->controller = app(OpsDashboardController::class);
        $this->service = app(OpsDashboardService::class);
        $this->repo = new OpsDashboardPatientEloquentRepository();

        $this->date = Carbon::today();

        //to test SetupTestCustomer Trait
        $this->practice = $this->createPractice();
        $this->location = $this->createLocation($this->practice);
        $this->patient = $this->createPatient($this->practice);
        $this->provider = $this->createProvider($this->practice);
        $this->total = $this->createTestCustomerData();
    }
}
