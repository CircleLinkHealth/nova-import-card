<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ApiPatient\Http\Controllers;

use App\Services\CPM\CpmLifestyleService;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UserLifestyleController extends Controller
{
    /**
     * @var CpmLifestyleService
     */
    protected $lifestyleService;

    /**
     * UserLifestyleController constructor.
     */
    public function __construct(CpmLifestyleService $lifestyleService)
    {
        $this->lifestyleService = $lifestyleService;
    }

    public function addLifestyle($userId, Request $request)
    {
        $lifestyleId = $request->input('lifestyleId');
        if ($userId && $lifestyleId) {
            return $this->lifestyleService->addLifestyleToPatient($lifestyleId, $userId);
        }

        return \response('"lifestyleId" and "userId" are important');
    }

    public function getLifestyles($userId)
    {
        if ($userId) {
            return User::with('cpmLifestyles')->whereId($userId)->firstOrFail()->cpmLifestyles;
        }

        return \response('"userId" is important');
    }

    public function removeLifestyle($userId, $lifestyleId)
    {
        if ($userId && $lifestyleId) {
            return $this->lifestyleService->removeLifestyleFromPatient($lifestyleId, $userId);
        }

        return \response('"lifestyleId" and "userId" are important');
    }
}
