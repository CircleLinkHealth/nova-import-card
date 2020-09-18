<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Revisionable\Entities\Revision;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ShowRevisionsController extends Controller
{
    /**
     * Show all the activity registered from using CPM.
     *
     * @return Response
     */
    public function allActivity(Request $request)
    {
        $phiOnly = $request->routeIs('revisions.phi.activity');

        if ($request->has('date-from')) {
            $startDate = new Carbon($request['date-from']);
        } else {
            $startDate = Carbon::today()->subWeeks(4);
        }

        if ($request->has('date-to')) {
            $endDate = new Carbon($request['date-to']);
        } else {
            $endDate = Carbon::today();
        }

        if ($request->has('revisionable_type')) {
            $revisionableType = $request['revisionable_type'];
        } else {
            $revisionableType = null;
        }

        if ($request->has('revisionable_id')) {
            $revisionableId = $request['revisionable_id'];
        } else {
            $revisionableId = null;
        }

        //validate input
        $errors = collect();
        if ($endDate->lessThan($startDate)) {
            $errors->push('Invalid date range.');
        }

        $startDate->setTime(0, 0);
        $endDate->setTime(23, 59, 59);

        $revisions = collect();
        if ($errors->isEmpty()) {
            $revisions = Revision::where('updated_at', '>=', $startDate->toDateTimeString())
                ->where('updated_at', '<=', $endDate->toDateTimeString())
                ->when($phiOnly, function ($q) use ($revisionableType) {
                    $q->where('is_phi', '=', true);
                })
                ->when((bool) $revisionableType, function ($q) use ($revisionableType) {
                    $q->where('revisionable_type', '=', $revisionableType);
                })
                ->when((bool) $revisionableId, function ($q) use ($revisionableId) {
                    $q->where('revisionable_id', '=', $revisionableId);
                })
                ->orderBy('updated_at', 'desc')
                ->paginate(20);
        }

        return view('admin.allActivity.index', compact([
            'errors',
            'startDate',
            'endDate',
            'revisions',
        ]));
    }

    /**
     * Show all PHI related revisions for a patient.
     *
     * @param $userId
     */
    public function phi(Request $request, $userId)
    {
        if ($request->has('date-from')) {
            $startDate = new Carbon($request['date-from']);
        } else {
            $startDate = Carbon::createFromDate(2000, 1, 1);
        }

        if ($request->has('date-to')) {
            $endDate = new Carbon($request['date-to']);
        } else {
            $endDate = Carbon::today();
        }

        $patientInfoId = optional(Patient::whereUserId($userId)->first())->id;
        $user          = User::withTrashed()
            ->find($userId);

        $startDate->setTime(0, 0);
        $endDate->setTime(23, 59, 59);

        $revisions = Revision::where('updated_at', '>=', $startDate->toDateTimeString())
            ->where('updated_at', '<=', $endDate->toDateTimeString())
            ->where(function ($q) use ($userId, $patientInfoId) {
                $q->where(function ($q) use ($patientInfoId) {
                    $q->where('revisionable_type', Patient::class)
                        ->where('revisionable_id', $patientInfoId)
                        ->whereIn('key', (new Patient())->phi);
                })->orWhere(function ($q) use ($userId) {
                    $q->where('revisionable_type', User::class)
                        ->where('revisionable_id', $userId)
                        ->whereIn('key', (new User())->phi);
                });
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        $submitUrl = route('revisions.patient.phi', $userId);

        $errors = collect();

        return view('admin.allActivity.index', compact([
            'errors',
            'startDate',
            'endDate',
            'revisions',
            'submitUrl',
            'user',
        ]));
    }
}
