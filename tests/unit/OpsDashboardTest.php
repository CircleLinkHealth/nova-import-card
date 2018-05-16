<?php

namespace Tests\Unit;

use App\Http\Controllers\OpsDashboardController;
use App\Models\CPM\CpmProblem;
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
    private $data;
    private $practice;
    private $patients;

    public function test_ops_Dashboard_ccm_time_patients(){



    }

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


        $this->assertInstanceOf('Illuminate\Support\Collection', $rows);

    }


    public function setUp()
    {
        parent::setUp();

        $this->controller = app(OpsDashboardController::class);
        $this->service = app(OpsDashboardService::class);
        $this->repo = new OpsDashboardPatientEloquentRepository();
        $this->date = Carbon::today();

        $this->data = $this->createTestCustomerData(100);
        $this->patients = $this->data['patients'];
        $this->practice = $this->practice;

    }
}
