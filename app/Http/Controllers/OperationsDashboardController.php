<?php

namespace App\Http\Controllers;

use App\Services\OperationsDashboardService;

class OperationsDashboardController extends Controller
{

    private $service;

    /**
     * OperationsDashboardController constructor.
     *
     * @param OperationsDashboardService $service
     */
    public function __construct(
        OperationsDashboardService $service
    ) {
        $this->service = $service;

    }


    public function index()
    {

    }
}
