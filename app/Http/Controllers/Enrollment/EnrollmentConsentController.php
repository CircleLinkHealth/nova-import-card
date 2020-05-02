<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Http\Request;

class EnrollmentConsentController extends Controller
{
    public function create($invite_code)
    {
        $enrollee                   = Enrollee::whereInviteCode($invite_code)->first();
        $enrollee->invite_opened_at = Carbon::now()->toDateTimeString();
        $enrollee->save();

        if (is_null($enrollee)) {
            return view('errors.enrollmentConsentUrlError');
        }

        return view('enrollment-consent.create', ['enrollee' => $enrollee]);
    }

    /**
     * @return mixed
     */
    public function index()
    {
        $enrollees = Enrollee::with('careAmbassador', 'practice', 'provider')
            ->where('status', '!=', 'enrolled')
            ->where('attempt_count', '<', 3)
            ->get();

        $formatted = [];
        $count     = 0;

        foreach ($enrollees as $enrollee) {
            $status = '';

            //if the patient has a ca_id, then it's a phone call
            if (null != $enrollee->care_ambassador_user_id) {
                //if the patient wasn't reached, show how many attempts were made
                if (Enrollee::UNREACHABLE == $enrollee->status) {
                    $status = 'Call:'.$enrollee->attempt_count.'x';
                } elseif (in_array($enrollee->status, [
                    Enrollee::REJECTED,
                    Enrollee::SOFT_REJECTED,
                ])) {
                    $status = 'Call: Declined';
                } elseif (Enrollee::CONSENTED == $enrollee->status) {
                    $status = 'Call: Consented';
                }
            }

            if (Enrollee::TO_CALL == $enrollee->status) {
                $status = 'Call: To Call';
            }

            if (Enrollee::TO_SMS == $enrollee->status) {
                if (null == $enrollee->invite_sent_at) {
                    $status = 'SMS: To SMS';
                } else {
                    $status = 'SMS:'.$enrollee->attempt_count.'x';
                }
            }

            if (null != $enrollee->invite_sent_at && 'consented' == $enrollee->status) {
                $status = 'SMS: Consented';
            }

            $days = (null == $enrollee->preferred_days)
                ? 'N/A'
                : $enrollee->preferred_days;
            $times = (null == $enrollee->preferred_days)
                ? 'N/A'
                : $enrollee->preferred_window;

            $careAmbassador = optional($enrollee->careAmbassador)->user;

            $formatted[$count] = [
                'name'      => $enrollee->first_name.' '.$enrollee->last_name,
                'program'   => ucwords(optional($enrollee->practice)->name),
                'provider'  => ucwords($enrollee->getProviderFullNameAttribute()),
                'has_copay' => $enrollee->has_copay
                    ? 'Yes'
                    : 'No',
                'status'                   => $status,
                'total_time_spent'         => $enrollee->total_time_spent ?? 0,
                'care_ambassador'          => ucwords(optional($careAmbassador)->getFullName() ?? null),
                'last_call_outcome'        => ucwords($enrollee->last_call_outcome),
                'last_call_outcome_reason' => ucwords($enrollee->last_call_outcome_reason),
                'mrn_number'               => ucwords($enrollee->mrn_number),
                'dob'                      => ucwords($enrollee->dob),
                'phone'                    => ucwords($enrollee->primary_phone),
                'invite_sent_at'           => ucwords($enrollee->invite_sent_at),
                'invite_opened_at'         => ucwords($enrollee->invite_opened_at),
                'last_attempt_at'          => ucwords($enrollee->last_attempt_at),
                'consented_at'             => ucwords($enrollee->consented_at),
                'preferred_days'           => $days,
                'preferred_window'         => $times,
                'agent_phone'              => $enrollee->getAgentAttribute(Enrollee::AGENT_PHONE_KEY),
                'agent_name'               => $enrollee->getAgentAttribute(Enrollee::AGENT_NAME_KEY),
                'agent_email'              => $enrollee->getAgentAttribute(Enrollee::AGENT_EMAIL_KEY),
                'agent_relationship'       => $enrollee->getAgentAttribute(Enrollee::AGENT_RELATIONSHIP_KEY),
            ];

            ++$count;
        }

        $formatted = collect($formatted);
        $formatted->sortByDesc('date');

        return datatables()->collection($formatted)->make(true);
    }

    public function makeEnrollmentReport()
    {
        return view('admin.reports.enrollment.enrollment-list');
    }

    public function store(Request $request)
    {
        $input = $request->input();

        $enrollee = Enrollee::find($input['enrollee_id']);

        $enrollee->consented_at = Carbon::parse($input['consented_at'])->toDateTimeString();
        $enrollee->status       = 'consented';
        $enrollee->save();

        return json_encode($enrollee);
    }

    public function update(Request $request)
    {
        $input = $request->input();

        $enrollee = Enrollee::find($input['enrollee_id']);

        if (isset($input['days'])) {
            $enrollee->preferred_days = implode(', ', $input['days']);
        }

        if (isset($input['times'])) {
            $enrollee->preferred_window = implode(', ', $input['times']);
        }

        $enrollee->save();

        return view('enrollment-consent.thanks', ['enrollee' => $enrollee]);
    }
}
