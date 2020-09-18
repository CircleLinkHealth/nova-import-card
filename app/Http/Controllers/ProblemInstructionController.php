<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Services\CCD\CcdProblemService;
use CircleLinkHealth\SharedModels\Services\CpmInstructionService;
use CircleLinkHealth\SharedModels\Services\CpmProblemService;
use App\Services\CPM\CpmProblemUserService;
use App\Services\UserService;
use Illuminate\Http\Request;

class ProblemInstructionController extends Controller
{
    private $cpmInstructionService;
    private $cpmProblemService;
    private $cpmProblemUserService;
    private $userService;

    /**
     * ProblemInstructionController constructor.
     */
    public function __construct(
        CpmInstructionService $cpmInstructionService,
        CpmProblemService $cpmProblemService,
        CpmProblemUserService $cpmProblemUserService,
        UserService $userService,
        CcdProblemService $ccdProblemService
    ) {
        $this->userService           = $userService;
        $this->cpmInstructionService = $cpmInstructionService;
        $this->cpmProblemService     = $cpmProblemService;
        $this->cpmProblemUserService = $cpmProblemUserService;
        $this->ccdProblemService     = $ccdProblemService;
    }

    public function addInstructionProblem(Request $request)
    {
        $patientId     = $request->route()->patientId;
        $cpmProblemId  = $request->route()->cpmId;
        $instructionId = $request->input('instructionId');

        try {
            $patient     = $this->userService->repo()->model()->find($patientId);
            $problem     = $this->cpmProblemService->repo()->model()->find($cpmProblemId);
            $instruction = $this->cpmInstructionService->repo()->model()->find($instructionId);

            if ($patient && $problem && $instruction) {
                return response()->json($this->cpmProblemUserService->addInstructionToProblem($patientId, $cpmProblemId, $instructionId));
            }
            if ( ! $patient) {
                return $this->notFound('patient not found');
            }
            if ( ! $problem) {
                return $this->notFound('cpm problem not found');
            }

            return $this->notFound('instruction not found');
        } catch (Exception $ex) {
            return $this->error($ex);
        }
    }

    public function addInstructionToCcdProblem($patientId, $problemId, Request $request)
    {
        $instructionId = $request->input('instructionId');

        try {
            $patient     = $this->userService->repo()->model()->find($patientId);
            $problem     = $this->ccdProblemService->repo()->model()->where(['id' => $problemId]);
            $instruction = $this->cpmInstructionService->repo()->model()->find($instructionId);

            if ($patient && ($problem->count() > 0) && $instruction) {
                $problem->update([
                    'cpm_instruction_id' => $instructionId,
                ]);

                return response()->json($this->ccdProblemService->problem($problemId));
            }
            if ( ! $patient) {
                return $this->notFound('patient not found');
            }
            if ( ! $problem) {
                return $this->notFound('cpm problem not found');
            }

            return $this->notFound('instruction not found');
        } catch (Exception $ex) {
            return $this->error($ex);
        }
    }

    /** edits an existing cpm-instruction */
    public function edit(Request $request)
    {
        $id         = $request->route()->id;
        $name       = $request->input('name');
        $is_default = $request->input('is_default');
        if ($id && '' != $id) {
            $instructions = $this->cpmInstructionService->repo()->model() - where(['id' => $id]);
            if ($name && '' != $name) {
                $instructions->update(['name' => $name]);
            }
            if ($is_default) {
                $instructions->update(['is_default' => $is_default]);
            }
            $instruction = $instructions->first();
            if ($instruction) {
                return response()->json($instruction);
            }

            return $this->notFound();
        }

        return $this->badRequest('please provide a value for the [id] parameter');
    }

    /** returns paginated list of cpm-instructions */
    public function index()
    {
        return response()->json($this->cpmInstructionService->instructions());
    }

    /** returns a single cpm-instruction */
    public function instruction($instructionId)
    {
        $instruction = $this->cpmInstructionService->instruction($instructionId);
        if ($instruction) {
            return response()->json($instruction);
        }

        return $this->notFound();
    }

    public function removeInstructionFromCcdProblem($patientId, $problemId, $instructionId)
    {
        try {
            $patient     = $this->userService->repo()->model()->find($patientId);
            $problem     = $this->ccdProblemService->repo()->model()->where(['id', $problemId]);
            $instruction = $this->cpmInstructionService->repo()->model()->where(['id', $instructionId]);

            if ($patient && ($problem->count() > 0) && ($instruction->count() > 0)) {
                $problem->update([
                    'cpm_instruction_id' => null,
                ]);

                return response()->json($this->ccdProblemService->problem($problemId));
            }
            if ( ! $patient) {
                return $this->notFound('patient not found');
            }
            if ( ! $problem) {
                return $this->notFound('cpm problem not found');
            }

            return $this->notFound('instruction not found');
        } catch (Exception $ex) {
            return $this->error($ex);
        }
    }

    public function removeInstructionProblem(Request $request)
    {
        $patientId     = $request->route()->patientId;
        $cpmProblemId  = $request->route()->cpmId;
        $instructionId = $request->route()->instructionId;

        try {
            $patient     = $this->userService->repo()->model()->find($patientId);
            $problem     = $this->cpmProblemService->repo()->model()->find($cpmProblemId);
            $instruction = $this->cpmInstructionService->repo()->model()->find($instructionId);

            if ($patient && $problem && $instruction) {
                $this->cpmProblemUserService->removeInstructionFromProblem($patientId, $cpmProblemId, $instructionId);

                return response()->json([
                    'message' => 'success',
                ]);
            }
            if ( ! $patient) {
                return $this->notFound('patient not found');
            }
            if ( ! $problem) {
                return $this->notFound('cpm problem not found');
            }

            return $this->notFound('instruction not found');
        } catch (Exception $ex) {
            return $this->error($ex);
        }
    }

    /** creates a cpm-instruction */
    public function store(Request $request)
    {
        $name = $request->input('name');
        if ($name && '' != $name) {
            try {
                $instruction = $this->cpmInstructionService->create($name);
                if ($instruction) {
                    return response()->json($instruction);
                }

                return $this->error('could not create instruction');
            } catch (Exception $ex) {
                return $this->error('error when creating new instruction', $ex);
            }
        } else {
            return $this->badRequest('please provide a value for the [name] parameter');
        }
    }
}
