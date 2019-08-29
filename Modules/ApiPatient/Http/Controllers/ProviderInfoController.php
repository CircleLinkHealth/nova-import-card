<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ApiPatient\Http\Controllers;

use App\Services\ProviderInfoService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ProviderInfoController extends Controller
{
    /**
     * @var ProviderInfoService
     */
    protected $providerService;
    
    /**
     * ProviderInfoController constructor.
     *
     * @param ProviderInfoService $providerService
     */
    public function __construct(ProviderInfoService $providerService)
    {
        $this->providerService = $providerService;
    }
    
    public function show($userId)
    {
        return \response($this->providerService->getPatientProviders($userId));
    }
}
