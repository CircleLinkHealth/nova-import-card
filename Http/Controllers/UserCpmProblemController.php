<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ApiPatient\Http\Controllers;

use App\Services\CCD\CcdProblemService;
use App\Services\CPM\CpmProblemUserService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class UserCpmProblemController extends Controller
{
    /**
     * @var CcdProblemService
     */
    protected $ccdProblemService;
    /**
     * @var CpmProblemUserService
     */
    protected $cpmProblemUserService;

    /**
     * UserCpmProblemController constructor.
     */
    public function __construct(CcdProblemService $ccdProblemService, CpmProblemUserService $cpmProblemUserService)
    {
        $this->cpmProblemUserService = $cpmProblemUserService;
        $this->ccdProblemService     = $ccdProblemService;
    }

    public function addCpmProblem($userId, Request $request)
    {
        $cpmProblemId = $request->input('cpmProblemId');
        if ($userId && $cpmProblemId) {
            $this->cpmProblemUserService->addProblemToPatient($userId, $cpmProblemId);

            return $this->getCpmProblems($userId);
        }

        return \response('"userId" and "cpmProblemId" are important');
    }

    public function getCpmProblems($userId)
    {
        return response()->json($this->cpmProblemUserService->getPatientProblems($userId));
    }

    public function getProblems($userId)
    {
        $cpmProblems = $this->cpmProblemUserService->getPatientProblems($userId)->map(function ($p) {
            $p['type'] = 'cpm';

            return $p;
        });
        $ccdProblems = $this->ccdProblemService->getPatientProblems($userId)->map(function ($p) {
            $p['type'] = 'ccd';

            return $p;
        });

        return response()->json($cpmProblems->concat($ccdProblems));
    }

    public function removeCpmProblem($userId, $cpmId)
    {
        if ($userId && $cpmId) {
            $this->cpmProblemUserService->removeProblemFromPatient($userId, $cpmId);

            return $this->getCpmProblems($userId);
        }

        return \response('"userId" and "cpmId" are important');
    }
}
