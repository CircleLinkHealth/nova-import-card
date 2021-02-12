<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Http\Controllers;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\PatientMonthlyBillingStatus;
use CircleLinkHealth\CcmBilling\Http\Requests\ApproveBillablePatientsCountsRequest;
use CircleLinkHealth\CcmBilling\Http\Requests\ApproveBillablePatientsDataRequest;
use CircleLinkHealth\CcmBilling\Http\Requests\ApproveBillablePatientsIndexRequest;
use CircleLinkHealth\CcmBilling\Http\Requests\ApproveBillablePatientsOpenCloseMonthRequest;
use CircleLinkHealth\CcmBilling\Http\Requests\ApproveBillablePatientsSetBillingStatusRequest;
use CircleLinkHealth\CcmBilling\Http\Requests\ApproveBillablePatientsSetPatientChargeableServicesRequest;
use CircleLinkHealth\CcmBilling\Http\Requests\ApproveBillablePatientsSetPracticeChargeableServicesRequest;
use CircleLinkHealth\CcmBilling\Http\Requests\ApproveBillablePatientsSuccessfulCallsCountRequest;
use CircleLinkHealth\CcmBilling\Services\ApproveBillablePatientsService;
use CircleLinkHealth\CcmBilling\Services\ApproveBillablePatientsServiceV3;
use CircleLinkHealth\Core\Traits\ApiReturnHelpers;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\SharedModels\Services\CpmProblemService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ApproveBillablePatientsController extends Controller
{
    use ApiReturnHelpers;

    public function __construct()
    {
    }

    public function closeMonth(ApproveBillablePatientsOpenCloseMonthRequest $request)
    {
        $practiceId = intval($request->input('practice_id'));
        $date       = Carbon::createFromFormat('M, Y', $request->input('date'));
        $user       = auth()->user();

        /** @var ApproveBillablePatientsService|ApproveBillablePatientsServiceV3 $service */
        $service = $this->getService($request);
        $updated = $service->closeMonth($user->id, $practiceId, $date->firstOfMonth());

        return response()->json([
            'updated' => $updated,
        ]);
    }

    public function counts(ApproveBillablePatientsCountsRequest $request)
    {
        $practiceId = intval($request->input('practice_id'));
        $date       = Carbon::createFromFormat('M, Y', $request->input('date'))->startOfMonth();

        /** @var ApproveBillablePatientsService|ApproveBillablePatientsServiceV3 $service */
        $service = $this->getService($request);
        $counts  = $service->counts($practiceId, $date->firstOfMonth());

        return response()->json($counts->toArray());
    }

    public function data(ApproveBillablePatientsDataRequest $request)
    {
        $practiceId = intval($request->input('practice_id'));
        $date       = Carbon::createFromFormat('M, Y', $request->input('date'))->startOfMonth();

        /** @var ApproveBillablePatientsService|ApproveBillablePatientsServiceV3 $service */
        $service = $this->getService($request);
        $month   = $service->getBillablePatientsForMonth($practiceId, $date);

        return response($month->summaries)->header('is-closed', (int) $month->isClosed);
    }

    public function index(ApproveBillablePatientsIndexRequest $request)
    {
        $practices = Practice::orderBy('display_name')
            ->select(['name', 'id', 'display_name'])
            ->with([
                'chargeableServices' => fn ($q) => $q->orderBy('order', 'asc'),
            ])
            ->authUserCanAccess(auth()->user()->isSoftwareOnly())
            ->active()
            ->get();

        $cpmProblems = (new CpmProblemService())->all();

        $currentMonth   = Carbon::now()->startOfMonth();
        $numberOfMonths = $this->getNumberOfMonths($request, $currentMonth);
        $dates          = [];

        for ($i = 0; $i <= $numberOfMonths; ++$i) {
            $date = $currentMonth->copy()->subMonths($i)->startOfMonth();

            $dates[] = [
                'label' => $date->format('F, Y'),
                'value' => $date->toDateString(),
            ];
        }

        $chargeableServices = ChargeableService::cached()->sortBy('order');
        $version            = $request->get('version', '2');

        return view('ccmbilling::billing', compact([
            'cpmProblems',
            'practices',
            'chargeableServices',
            'dates',
            'version',
        ]));
    }

    public function openMonth(ApproveBillablePatientsOpenCloseMonthRequest $request)
    {
        $practiceId = intval($request->input('practice_id'));
        $date       = Carbon::createFromFormat('M, Y', $request->input('date'));

        /** @var ApproveBillablePatientsService|ApproveBillablePatientsServiceV3 $service */
        $service = $this->getService($request);
        $updated = $service->openMonth($practiceId, $date->firstOfMonth());

        return response()->json([
            'updated' => $updated,
        ]);
    }

    public function setBillingStatus(ApproveBillablePatientsSetBillingStatusRequest $request)
    {
        $reportId  = intval($request->input('report_id'));
        $newStatus = $request->input('status');

        /** @var ApproveBillablePatientsService|ApproveBillablePatientsServiceV3 $service */
        $service  = $this->getService($request);
        $response = $service->setPatientBillingStatus($reportId, $newStatus);

        return $response ? $this->ok($response) : $this->error('there was an error');
    }

    public function setPatientChargeableServices(ApproveBillablePatientsSetPatientChargeableServicesRequest $request)
    {
        $reportId           = intval($request->input('report_id'));
        $chargeableServices = $request->input('patient_chargeable_services');

        /** @var ApproveBillablePatientsService|ApproveBillablePatientsServiceV3 $service */
        $service = $this->getService($request);
        $result  = $service->setPatientChargeableServices($reportId, $chargeableServices);

        return $result ? $this->ok() : $this->error('there was an error');
    }

    /**
     * @deprecated this feature will not be implemented in new version
     * @return \Illuminate\Http\JsonResponse
     */
    public function setPracticeChargeableServices(ApproveBillablePatientsSetPracticeChargeableServicesRequest $request)
    {
        $practiceId    = $request->input('practice_id');
        $date          = Carbon::createFromFormat('M, Y', $request->input('date'));
        $defaultCodeId = $request->input('default_code_id');
        $isDetach      = $request->has('detach');

        /** @var ApproveBillablePatientsService|ApproveBillablePatientsServiceV3 $service */
        $service = $this->getService($request);
        if ($service instanceof ApproveBillablePatientsServiceV3) {
            return $this->badRequest('not supported');
        }
        $summaries = $service->setPracticeChargeableServices($practiceId, $date->firstOfMonth(), $defaultCodeId, $isDetach);

        return $this->ok($summaries);
    }

    public function successfulCallsCount(ApproveBillablePatientsSuccessfulCallsCountRequest $request)
    {
        $patientIds = $request->input('patient_ids');
        $date       = Carbon::createFromFormat('M, Y', $request->input('date'));
        $response   = app(ApproveBillablePatientsServiceV3::class)->successfulCallsCount($patientIds, $date);

        return $this->ok($response);
    }

    private function getNumberOfMonths(Request $request, Carbon $startMonth)
    {
        $version = intval($request->input('version', 2));
        if ($version < 3) {
            $oldestSummary = PatientMonthlySummary::orderBy('created_at', 'asc')->first();
        } else {
            $oldestSummary = PatientMonthlyBillingStatus::orderBy('created_at', 'asc')->first();
        }

        if ( ! $oldestSummary) {
            return 12;
        }

        return $startMonth->diffInMonths($oldestSummary->created_at->startOfMonth()) ?? 12;
    }

    private function getService(Request $request)
    {
        $version = $request->input('version', 2);

        return (3 === intval($version)) ?
            app(ApproveBillablePatientsServiceV3::class) :
            app(ApproveBillablePatientsService::class);
    }
}
