<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ApiPatient\Http\Controllers;

use App\Services\CCD\CcdAllergyService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class AllergyController extends Controller
{
    /**
     * @var CcdAllergyService
     */
    protected $allergyService;

    public function __construct(CcdAllergyService $allergyService)
    {
        $this->allergyService = $allergyService;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int   $id
     * @param mixed $userId
     * @param mixed $allergyId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($userId, $allergyId)
    {
        if ($userId && $allergyId) {
            return response()->json($this->allergyService->deletePatientAllergy($userId, $allergyId));
        }

        return \response()->json('"userId" and "allergyId" are important');
    }

    /**
     * Show the specified resource.
     *
     * @param int   $id
     * @param mixed $userId
     *
     * @return Response
     */
    public function show($userId)
    {
        return response()->json($this->allergyService->patientAllergies($userId));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param mixed $userId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($userId, Request $request)
    {
        $name = $request->input('name');
        if ($name) {
            return response()->json($this->allergyService->addPatientAllergy($userId, $name));
        }

        return \response()->json('"name" is important', 400);
    }
}
