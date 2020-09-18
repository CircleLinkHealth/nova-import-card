<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Controllers\API\Admin;

use CircleLinkHealth\SharedModels\Entities\Call;
use App\Filters\CallFilters;
use App\Filters\PatientFilters;
use App\Http\Controllers\API\ApiController;
use App\Http\Resources\Call as CallResource;
use App\Http\Resources\UserResource;
use App\Services\Calls\ManagementService;
use App\Services\NoteService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CallsController extends ApiController
{
    private $noteService;
    private $service;

    public function __construct(ManagementService $service, NoteService $noteService)
    {
        $this->service     = $service;
        $this->noteService = $noteService;
    }

    /**
     * @SWG\GET(
     *     path="/admin/calls",
     *     tags={"calls"},
     *     summary="Get Calls Info",
     *     description="Display a listing of calls",
     *     @SWG\Header(header="X-Requested-With", type="String", default="XMLHttpRequest"),
     *     @SWG\Response(
     *         response="default",
     *         description="A listing of calls"
     *     )
     *   )
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, CallFilters $filters)
    {
        $rows  = $request->input('rows');
        $calls = Call::whereHas('inboundUser', function ($q) {
            $q->whereHas('primaryPractice', function ($q) {
                $q->where('active', 1);
            })->whereHas('patientInfo', function ($q) {
                $q->where('ccm_status', Patient::ENROLLED);
            });
        })
            ->with('schedulerUser.roles')
            ->filter($filters)
            ->paginate($rows ?? 15);

        return CallResource::collection($calls);
    }

    public function patientsWithoutInboundCalls(PatientFilters $filters, $practiceId = null)
    {
        $patients = $this->service->getPatientsWithoutAnyInboundCalls($practiceId, Carbon::now())
            ->filter($filters)->get();

        if ($filters->isAutocomplete()) {
            return $patients->map(function ($patient) {
                return $patient->autocomplete();
            });
        }

        return UserResource::collection($patients);
    }

    public function patientsWithoutScheduledActivities(PatientFilters $filters, $practiceId = null)
    {
        $user = auth()->user();

        if ( ! $user->isAdmin()) {
            //if we have $practiceId, make sure that user has access to it
            if ($practiceId) {
                if ( ! $user->hasRoleForSite('software-only', $practiceId)) {
                    abort(403);
                }
            } else {
                //if no $practiceId, get all practice ids where user is software-only / practice admin
                $roleIds    = Role::getIdsFromNames(['software-only']);
                $practiceId = $user->practices(true, false, $roleIds)->pluck('id')->toArray();
            }
        }

        $patients = $this->service->getPatientsWithoutScheduledActivities($practiceId, Carbon::now())
            ->filter($filters)->get();

        if ($filters->isAutocomplete()) {
            return $patients->map(function ($patient) {
                return $patient->autocomplete();
            });
        }

        return UserResource::collection($patients);
    }

    public function patientsWithoutScheduledCalls(PatientFilters $filters, $practiceId = null)
    {
        $user = auth()->user();

        if ( ! $user->isAdmin()) {
            //if we have $practiceId, make sure that user has access to it
            if ($practiceId) {
                if ( ! $user->hasRoleForSite('software-only', $practiceId)) {
                    abort(403);
                }
            } else {
                //if no $practiceId, get all practice ids where user is software-only / practice admin
                $roleIds    = Role::getIdsFromNames(['software-only']);
                $practiceId = $user->practices(true, false, $roleIds)->pluck('id');
            }
        }

        $patients = $this->service->getPatientsWithoutScheduledCalls($practiceId, Carbon::now())
            ->filter($filters)->get();

        if ($filters->isAutocomplete()) {
            return $patients->map(function ($patient) {
                return $patient->autocomplete();
            });
        }

        return UserResource::collection($patients);
    }

    /**
     * Remove the calls with IDs from storage.
     *
     * @param string $ids
     *
     * @return \Illuminate\Http\Response
     */
    public function remove($ids)
    {
        if (Str::contains($ids, ',')) {
            $ids = explode(',', $ids);
        }

        if ( ! is_array($ids)) {
            $ids = [$ids];
        }

        Call::whereIn('id', $ids)
            ->delete();

        return response()->json($ids);
    }

    public function show($id)
    {
        return $this->json(Call::findOrFail($id));
    }
}
