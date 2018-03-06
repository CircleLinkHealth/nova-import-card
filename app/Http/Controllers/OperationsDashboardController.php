<?php

namespace App\Http\Controllers;

use App\Http\Requests\Request;
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
        //load all needed results for now(), then change accordingly

        //return view with data for now()
        return;

    }


    public function getPatientData(Request $request){

        //load page again with data from request

        //nothing for Practice unless selected


    }

}
