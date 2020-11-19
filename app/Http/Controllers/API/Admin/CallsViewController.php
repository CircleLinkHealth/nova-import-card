<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\API\Admin;

use App\Call;
use App\CallView;
use App\Filters\CallViewFilters;
use App\Http\Controllers\API\ApiController;
use App\Http\Resources\CallView as CallViewResource;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\TimeTracking\Entities\Activity;
use Illuminate\Http\Request;

class CallsViewController extends ApiController
{
    public function __construct()
    {
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
    public function index(Request $request, CallViewFilters $filters)
    {
        $rows  = $request->input('rows');
        $calls = CallView::filter($filters)
            ->paginate($rows ?? 15);

        return CallViewResource::collection($calls);
    }
    
    public function getPatientTimeAndCalls(Request $request){
        $data = [];
        $ids = $request->input();
        
        if (empty($ids)){
            return response()->json([]);
        }
        $activities = Activity::whereIn('patient_id', $ids)
            ->createdInMonth($thisMonth = Carbon::now()->startOfMonth(), 'performed_at')
            ->get();
        
        $calls = Call::whereIn('inbound_cpm_id', [$ids])
            ->where(function ($q) {
                $q->whereNull('type')
                    ->orWhere('type', '=', 'call')
                    ->orWhere('sub_type', '=', 'Call Back');
            })
            ->where('status', 'reached')
            ->createdInMonth($thisMonth, 'called_date')
            ->get();
        
        foreach ($ids as $patientId){
            $patientActivities = $activities->where('patient_id', $patientId);
            
            $data[$patientId] = [
                'ccm_total_time' => $patientActivities->whereIn('chargeable_service_id', ChargeableService::cached()->whereIn('code', ChargeableService::CCM_CODES)->pluck('id')->toArray())->sum('duration'),
                'bhi_total_time' => $patientActivities->where('chargeable_service_id', ChargeableService::cached()->firstWhere('code', ChargeableService::BHI)->id)->sum('duration'),
                'pcm_total_time' => $patientActivities->where('chargeable_service_id', ChargeableService::cached()->firstWhere('code', ChargeableService::PCM)->id)->sum('duration'),
                'rpm_total_time' => $patientActivities->where('chargeable_service_id', ChargeableService::cached()->whereIn('code', ChargeableService::RPM_CODES)->pluck('id')->toArray())->sum('duration'),
                'no_of_successful_calls' => $calls->where('inbound_cpm_id', $patientId)->count()
            ];
        }
        
        return response()->json($data);
    }
}
