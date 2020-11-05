<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Admin\Reports;

use App\CallView;
use App\Filters\CallViewFilters;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\BillingConstants;
use CircleLinkHealth\Core\Exports\FromArray;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use Facades\FriendsOfCat\LaravelFeatureFlags\Feature;
use Illuminate\Http\Request;

class CallReportController extends Controller
{
    public function exportXlsV2(Request $request, CallViewFilters $filters)
    {
        ini_set('memory_limit', '512M');

        $date = Carbon::now()->startOfMonth();

        $calls = CallView::filter($filters)
            ->get();

        if ($request->has('json')) {
            // interrupt request and return json
            return response()->json($calls);
        }

        $data = $this->generateXlsData($date, $calls);

        return $data->download($data->getFilename());
    }

    /**
     * @return int media id
     */
    public function generateXlsAndSaveToMedia(Carbon $date, CallViewFilters $filters)
    {
        $calls      = CallView::filter($filters)->get();
        $data       = $this->generateXlsData($date, $calls);
        $model      = SaasAccount::whereSlug('circlelink-health')->firstOrFail();
        $collection = "pam_{$date->toDateString()}";
        $media      = $data->storeAndAttachMediaTo($model, $collection);

        return $media->id;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
    }

    private function formatTime($time)
    {
        $seconds = $time;
        $H       = floor($seconds / 3600);
        $i       = ($seconds / 60) % 60;
        $s       = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $H, $i, $s);
    }

    private function generateXlsData($date, $calls)
    {
        $headings = [
            'id',
            'Type',
            'Nurse',
            'Patient',
            'Practice',
            'Activity Day',
            'Activity Start',
            'Activity End',
            'Preferred Call Days',
            'Last Call',
            'CCM Time',
            'BHI Time',
            'PCM Time',
            'RPM Time',
            'Successful Calls',
            'Billing Provider',
            'Scheduler',
        ];

        $rows = [];
        
        $billingRevampIsEnabled = Feature::isEnabled(BillingConstants::BILLING_REVAMP_FLAG);

        foreach ($calls as $call) {
            $rows[] = [
                $call->id,
                $call->type,
                $call->nurse,
                $call->patient,
                $call->practice,
                $call->scheduled_date,
                $call->call_time_start,
                $call->call_time_end,
                $call->preferredCallDaysToString(),
                $call->last_call,
                $this->formatTime($billingRevampIsEnabled ? $call->ccm_total_time : $call->pms_ccm_time),
                $this->formatTime($billingRevampIsEnabled ? $call->bhi_total_time : $call->pms_bhi_time),
                $this->formatTime($billingRevampIsEnabled ? $call->pcm_total_time : 0),
                $this->formatTime($billingRevampIsEnabled ? $call->rpm_total_time : 0),
                $call->no_of_successful_calls,
                $call->billing_provider,
                $call->scheduler,
            ];
        }

        $fileName = 'CLH-Report-'.$date.'.xls';

        return new FromArray($fileName, $rows, $headings);
    }
}
