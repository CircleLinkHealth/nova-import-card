<?php namespace App\Http\Controllers\Admin\Reports;

use App\Call;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Excel;
use Illuminate\Http\Request;

class CallReportController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
    }

    /**
     * export xls
     */

    public function exportxls(Request $request)
    {
        $date = Carbon::now()->startOfMonth();

        $calls = Call::with('inboundUser')
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
                    'patient_info.cur_month_activity_time',
                    'patient_info.last_successful_contact_time',
                    \DB::raw('DATE_FORMAT(patient_info.last_contact_time, "%Y-%m-%d") as last_contact_time'),
                    \DB::raw('coalesce(patient_info.no_call_attempts_since_last_success, "n/a") as no_call_attempts_since_last_success'),
                    'patient_info.ccm_status',
                    'patient_info.birth_date',
                    'patient_info.general_comment',
                    'patient_monthly_summaries.no_of_calls',
                    'patient_monthly_summaries.no_of_successful_calls',
                    \DB::raw('CONCAT_WS(", ", patient.last_name, patient.first_name) AS patient_name'),
                    'program.display_name AS program_name',
                    'billing_provider.display_name AS billing_provider'
                ]
            )
            ->where('calls.status', '=', 'scheduled')
            ->leftJoin('notes', 'calls.note_id', '=', 'notes.id')
            ->leftJoin('users AS nurse', 'calls.outbound_cpm_id', '=', 'nurse.id')
            ->leftJoin('users AS patient', 'calls.inbound_cpm_id', '=', 'patient.id')
            ->leftJoin('users AS scheduler_user', 'calls.scheduler', '=', 'scheduler_user.id')
            ->leftJoin('patient_info', 'calls.inbound_cpm_id', '=', 'patient_info.user_id')
            ->leftJoin('patient_monthly_summaries', function ($join) use ($date) {
                $join->on('patient_monthly_summaries.patient_info_id', '=', 'patient_info.id');
                $join->where('patient_monthly_summaries.month_year', '=', $date->format('Y-m-d'));
            })
            ->leftJoin('practices AS program', 'patient.program_id', '=', 'program.id')
            ->leftJoin('patient_care_team_members', function ($join) {
                $join->on('patient.id', '=', 'patient_care_team_members.user_id');
                $join->where('patient_care_team_members.type', '=', "billing_provider");
            })
            ->leftJoin(
                'users AS billing_provider',
                'patient_care_team_members.member_user_id',
                '=',
                'billing_provider.id'
            )
            ->groupBy('call_id')
            ->get();

        Excel::create('CLH-Report-' . $date, function ($excel) use ($date, $calls) {

            // Set the title
            $excel->setTitle('CLH Call Report - ' . $date);

            // Chain the setters
            $excel->setCreator('CLH System')
                ->setCompany('CircleLink Health');

            // Call them separately
            $excel->setDescription('CLH Call Report - ' . $date);

            // Our first sheet
            $excel->sheet('Sheet 1', function ($sheet) use ($calls) {
                $i = 0;
                // header
                $userColumns = [
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
                $sheet->appendRow($userColumns);

                foreach ($calls as $call) {
                    if ($call->inboundUser && $call->inboundUser->patientInfo) {
                        $ccmTime = substr($call->inboundUser->patientInfo->currentMonthCCMTime, 1);
                    } else {
                        $ccmTime = 'n/a';
                    }

                    if ($call->inboundUser && $call->inboundUser->patientInfo) {
                        if (is_null($call->inboundUser->patientInfo->no_call_attempts_since_last_success)) {
                            $noAttmpts = 'n/a';
                        } else if ($call->inboundUser->patientInfo->no_call_attempts_since_last_success > 0) {
                            $noAttmpts = $call->inboundUser->patientInfo->no_call_attempts_since_last_success . 'x Attempts';
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
                        7 => 'Su'
                    ];
                    $preferredCallDays =  'n/a';
                    if ($call->inboundUser && $call->inboundUser->patientInfo) {
                        $windowText = '';
                        $windows = $call->inboundUser->patientInfo->contactWindows()->get();
                        if ($windows) {
                            foreach ($days as $key => $val) {
                                foreach ($windows as $window) {
                                    if ($window->day_of_week == $key) {
                                        $windowText .= $days[$window->day_of_week] . ',';
                                    }
                                }
                            }
                        }
                        $preferredCallDays = rtrim($windowText, ',');
                    }

                    //dd($call);
                    $columns = [$call->call_id, $call->nurse_name, $call->patient_name, $call->program_name, $noAttmpts, $call->scheduled_date, $call->window_start, $call->window_end, $preferredCallDays, $call->last_contact_time, $ccmTime, $call->no_of_successful_calls, $call->ccm_status, $call->billing_provider, $call->birth_date, $call->scheduler_user_name];
                    $sheet->appendRow($columns);
                }
            });
        })->export('xls');
    }
}
