<?php

namespace App\Http\Controllers\CcdApi\Athena;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Services\AthenaAPI\APICalls;

class AthenaApiController extends Controller
{
    protected $api;

    public function __construct()
    {
        $this->api = new APICalls();
    }
    
    public function getCcd($patientId = 4185, $departmentId = 1)
    {
        $this->api->getCcd($patientId, $departmentId);
    }
}
