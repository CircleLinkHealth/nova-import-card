<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Services\CcdaService;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Http\Request;

class CcdaController extends Controller
{
    private $ccdaService;

    public function __construct(CcdaService $ccdaService)
    {
        $this->ccdaService = $ccdaService;
    }

    public function index()
    {
        return $this->ccdaService->ccda();
    }

    public function show($id)
    {
        return $this->ccdaService->ccda($id);
    }

    public function store(Request $request)
    {
        $ccda              = new Ccda();
        $ccda->json        = $request->input('json');
        $xml               = $request->input('xml');
        $ccda->practice_id = $request->practice_id;
        $ccda->user_id     = auth()->user()->id;
        if ($ccda->user_id && ($xml || $ccda->json)) {
            return $this->ccdaService->create($ccda, $xml);
        }

        return $this->badRequest('"user_id" and one of "xml" and "json" are compulsory fields');
    }
}
