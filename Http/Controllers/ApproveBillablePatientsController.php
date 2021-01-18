<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Http\Controllers;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Http\Requests\ApproveBillablePatientsRequest;
use CircleLinkHealth\CcmBilling\Services\ApproveBillablePatientsService;
use CircleLinkHealth\CcmBilling\Services\ApproveBillablePatientsServiceV3;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\SharedModels\Services\CpmProblemService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ApproveBillablePatientsController extends Controller
{
    public function __construct()
    {
    }

    public function closeMonth(ApproveBillablePatientsRequest $request)
    {
        $practiceId = $request->input('practice_id');
        $date       = Carbon::createFromFormat('M, Y', $request->input('date'));
        $user       = auth()->user();

        /** @var ApproveBillablePatientsService|ApproveBillablePatientsServiceV3 $service */
        $service = $this->getService($request);
        $updated = $service->closeMonth($user->id, $practiceId, $date->firstOfMonth());

        return response()->json([
            'updated' => $updated,
        ]);
    }

    public function counts(ApproveBillablePatientsRequest $request)
    {
        $practiceId = $request['practice_id'];
        $date       = Carbon::createFromFormat('M, Y', $request->input('date'))->startOfMonth();

        /** @var ApproveBillablePatientsService|ApproveBillablePatientsServiceV3 $service */
        $service = $this->getService($request);
        $counts  = $service->counts($practiceId, $date->firstOfMonth());

        return response()->json($counts->toArray());
    }

    public function data(ApproveBillablePatientsRequest $request)
    {
        $practiceId = $request->input('practice_id');
        $date       = Carbon::createFromFormat('M, Y', $request->input('date'))->startOfMonth();

        /** @var ApproveBillablePatientsService|ApproveBillablePatientsServiceV3 $service */
        $service = $this->getService($request);
        $month   = $service->getBillablePatientsForMonth($practiceId, $date);

        return response($month->summaries)->header('is-closed', (int) $month->isClosed);
    }

    /**
     * Show the page to choose a practice and generate approvable billing reports.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $practices = Practice::orderBy('display_name')
            ->select(['name', 'id', 'display_name'])
            ->with('chargeableServices')
            ->authUserCanAccess(auth()->user()->isSoftwareOnly())
            ->active()
            ->get();

        $cpmProblems = (new CpmProblemService())->all();

        $currentMonth = Carbon::now()->startOfMonth();

        $dates = [];

        $oldestSummary = PatientMonthlySummary::orderBy('created_at', 'asc')->first();

        $numberOfMonths = $currentMonth->diffInMonths($oldestSummary->created_at->copy()->startOfMonth()) ?? 12;

        for ($i = 0; $i <= $numberOfMonths; ++$i) {
            $date = $currentMonth->copy()->subMonth()->startOfMonth();

            $dates[] = [
                'label' => $date->format('F, Y'),
                'value' => $date->toDateString(),
            ];
        }

        $chargeableServices = ChargeableService::cached();
        $version            = $request->get('version', '2');

        return view('ccmbilling::billing', compact([
            'cpmProblems',
            'practices',
            'chargeableServices',
            'dates',
            'version',
        ]));
    }

    private function getService(Request $request)
    {
        $version = $request->input('version', 2);

        return (3 === intval($version)) ?
            app(ApproveBillablePatientsServiceV3::class) :
            app(ApproveBillablePatientsService::class);
    }
}
