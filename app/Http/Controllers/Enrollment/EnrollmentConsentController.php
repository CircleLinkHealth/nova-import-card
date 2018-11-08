<?php

namespace App\Http\Controllers\Enrollment;

use App\Enrollee;
use App\Http\Controllers\Controller;
use App\Practice;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EnrollmentConsentController extends Controller
{

    /**
     * @return mixed
     */
    public function index()
    {

        //todo change to Enrollee
        $enrollees = Enrollee
            ::where('status', '!=', 'enrolled')
            ->where('attempt_count', '<', 3)
            ->get();

        $formatted = [];
        $count = 0;

        foreach ($enrollees as $enrollee) {
            $status = '';

            //if the patient has a ca_id, then it's a phone call
            if ($enrollee->care_ambassador_id != null) {
                //if the patient wasn't reached, show how many attempts were made
                if ($enrollee->status == 'utc') {
                    $status = 'Call:' . $enrollee->attempt_count . 'x';
                } elseif ($enrollee->status == 'rejected') {
                    $status = 'Call: Declined';
                } elseif ($enrollee->status == 'consented') {
                    $status = 'Call: Consented';
                }
            }

            if ($enrollee->status == 'call_queue') {
                $status = 'Call: To Call';
            }

            if ($enrollee->status == 'sms_queue') {
                if ($enrollee->invite_sent_at == null) {
                    $status = 'SMS: To SMS';
                } else {
                    $status = 'SMS:' . $enrollee->attempt_count . 'x';
                }
            }

            if ($enrollee->invite_sent_at != null && $enrollee->status == 'consented') {
                $status = 'SMS: Consented';
            }

            $days = ($enrollee->preferred_days == null) ? 'N/A' : $enrollee->preferred_days;
            $times = ($enrollee->preferred_days == null) ? 'N/A' : $enrollee->preferred_window;

            $formatted[$count] = [

                'name'                     => $enrollee->first_name . ' ' . $enrollee->last_name,
                'program'                  => ucwords(Practice::find($enrollee->practice_id)->name),
                'provider'                 => ucwords(User::find($enrollee->provider_id)->getFullName() ?? null),
                'has_copay'                => $enrollee->has_copay
                    ? 'Yes'
                    : 'No',
                'status'                   => $status,
                'care_ambassador'          => ucwords($enrollee->careAmbassador->user->getFullName() ?? null),
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
                'preferred_window'         => $times

            ];

            $count++;
        }

        $formatted = collect($formatted);
        $formatted->sortByDesc('date');

        return datatables()->collection($formatted)->make(true);
    }

    public function makeEnrollmentReport()
    {

        return view('admin.reports.enrollment.enrollment-list');
    }

    public function create($invite_code)
    {

        $enrollee = Enrollee::whereInviteCode($invite_code)->first();
        $enrollee->invite_opened_at = Carbon::now()->toDateTimeString();
        $enrollee->save();

        if (is_null($enrollee)) {
            return view('errors.enrollmentConsentUrlError');
        }

        return view('enrollment-consent.create', ['enrollee' => $enrollee]);
    }

    public function store(Request $request)
    {

        $input = $request->input();

        $enrollee = Enrollee::find($input['enrollee_id']);

        $enrollee->consented_at = Carbon::parse($input['consented_at'])->toDateTimeString();
        $enrollee->status = 'consented';
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
