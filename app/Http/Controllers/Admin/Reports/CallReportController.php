<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Admin\Reports;

use App\Call;
use App\CallView;
use App\Filters\CallFilters;
use App\Filters\CallViewFilters;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use CircleLinkHealth\Core\Exports\FromArray;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class CallReportController extends Controller
{
    /**
     * export xls.
     */
    public function exportxls(Request $request, CallFilters $filters)
    {
        $date = Carbon::now()->startOfMonth();

        if ($request->has('json')) {
            // interrupt request and return json
            return response()->json($this->callsQuery($filters, $date)->get());
        }

        $headings = [
            'id',
            'Nurse',
            'Patient',
            'Practice',
            'Last Call Status',
            'Next Call',
            'Call Time Start',
            'Call Time End',
            'Preferred Call Days',
            'Last Call',
            'CCM Time',
            'Successful Calls',
            'Patient Status',
            'Billing Provider',
            'DOB',
            'Scheduler',
        ];

        $rows = [];

        $this->callsQuery($filters, $date)
            ->chunkById(500, function (Collection $calls) use (&$rows, &$headings) {
                $calls->each(function ($call) use (&$rows, &$headings) {
                    if ($call->inboundUser) {
                        $ccmTime = $call->inboundUser->formattedCcmTime();
                    } else {
                        $ccmTime = 'n/a';
                    }

                    if ($call->inboundUser && $call->inboundUser->patientInfo) {
                        if (is_null($call->inboundUser->patientInfo->no_call_attempts_since_last_success)) {
                            $noAttmpts = 'n/a';
                        } elseif ($call->inboundUser->patientInfo->no_call_attempts_since_last_success > 0) {
                            $noAttmpts = $call->inboundUser->patientInfo->no_call_attempts_since_last_success.'x Attempts';
                        } else {
                            $noAttmpts = 'Success';
                        }
                    }
                    // call days
                    $days = [
                        1 => 'M',
                        2 => 'Tu',
                        3 => 'W',
                        4 => 'Th',
                        5 => 'F',
                        6 => 'Sa',
                        7 => 'Su',
                    ];
                    $preferredCallDays = 'n/a';
                    if ($call->inboundUser && $call->inboundUser->patientInfo) {
                        $windowText = '';
                        $windows = $call->inboundUser->patientInfo->contactWindows()->get();
                        if ($windows) {
                            foreach ($days as $key => $val) {
                                foreach ($windows as $window) {
                                    if ($window->day_of_week == $key) {
                                        $windowText .= $days[$window->day_of_week].',';
                                    }
                                }
                            }
                        }
                        $preferredCallDays = rtrim($windowText, ',');
                    }

                    $rows[] = [
                        $call->call_id,
                        $call->nurse_name,
                        $call->patient_name,
                        $call->program_name,
                        $noAttmpts,
                        $call->scheduled_date,
                        $call->window_start,
                        $call->window_end,
                        $preferredCallDays,
                        $call->last_contact_time,
                        $ccmTime,
                        $call->no_of_successful_calls,
                        $call->ccm_status,
                        $call->billing_provider,
                        $call->birth_date,
                        $call->scheduler_user_name,
                    ];
                });
            }, 'calls.id');

        $fileName = 'CLH-Report-'.$date.'.xls';

        return (new FromArray($fileName, $rows, $headings))->download($fileName);
    }

    public function exportXlsV2(Request $request, CallViewFilters $filters)
    {
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

    private function callsQuery(CallFilters $filters, Carbon $date)
    {
        return Call::filter($filters)->with('inboundUser')
            ->with('outboundUser')
            ->with('note')
            ->select(
                [
                    \DB::raw('coalesce(nurse.display_name, "unassigned") as nurse_name'),
                    'calls.id AS call_id',
                    'calls.status',
                    'calls.outbound_cpm_id',
                    'calls.inbound_cpm_id',
                    'calls.scheduled_date',
                    'calls.window_start',
                    'calls.window_end',
                    'calls.window_end AS window_end_value',
                    'calls.attempt_note',
                    'notes.type AS note_type',
                    'notes.body AS note_body',
                    'notes.performed_at AS note_datetime',
                    'calls.note_id',
                    'calls.scheduler AS scheduler',
                    'scheduler_user.display_name AS scheduler_user_name',
                    'patient_monthly_summaries.ccm_time',
                    'patient_info.last_successful_contact_time',
                    \DB::raw('DATE_FORMAT(patient_info.last_contact_time, "%Y-%m-%d") as last_contact_time'),
                    \DB::raw(
                        'coalesce(patient_info.no_call_attempts_since_last_success, "n/a") as no_call_attempts_since_last_success'
                    ),
                    'patient_info.ccm_status',
                    'patient_info.birth_date',
                    'patient_info.general_comment',
                    'patient_monthly_summaries.no_of_calls',
                    'patient_monthly_summaries.no_of_successful_calls',
                    \DB::raw('CONCAT_WS(", ", patient.last_name, patient.first_name) AS patient_name'),
                    'program.display_name AS program_name',
                    'billing_provider.display_name AS billing_provider',
                ]
            )
            ->where('calls.status', '=', 'scheduled')
            ->leftJoin('notes', 'calls.note_id', '=', 'notes.id')
            ->leftJoin('users AS nurse', 'calls.outbound_cpm_id', '=', 'nurse.id')
            ->leftJoin('users AS patient', 'calls.inbound_cpm_id', '=', 'patient.id')
            ->leftJoin('users AS scheduler_user', 'calls.scheduler', '=', 'scheduler_user.id')
            ->leftJoin('patient_info', 'calls.inbound_cpm_id', '=', 'patient_info.user_id')
            ->leftJoin(
                'patient_monthly_summaries',
                function ($join) use ($date) {
                    $join->on('patient_monthly_summaries.patient_id', '=', 'patient.id');
                    $join->where('patient_monthly_summaries.month_year', '=', $date->format('Y-m-d'));
                }
            )
            ->leftJoin('practices AS program', 'patient.program_id', '=', 'program.id')
            ->leftJoin(
                'patient_care_team_members',
                function ($join) {
                    $join->on('patient.id', '=', 'patient_care_team_members.user_id');
                    $join->where('patient_care_team_members.type', '=', 'billing_provider');
                }
            )
            ->leftJoin(
                'users AS billing_provider',
                'patient_care_team_members.member_user_id',
                '=',
                'billing_provider.id'
            )
            ->groupBy('call_id');
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
            'Successful Calls',
            'Billing Provider',
            'Scheduler',
        ];

        $rows = [];

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
                $this->formatTime($call->ccm_time),
                $this->formatTime($call->bhi_time),
                $call->no_of_successful_calls,
                $call->billing_provider,
                $call->scheduler,
            ];
        }

        $fileName = 'CLH-Report-'.$date.'.xls';

        return new FromArray($fileName, $rows, $headings);
    }
}
