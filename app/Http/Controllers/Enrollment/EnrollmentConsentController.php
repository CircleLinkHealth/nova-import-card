<?php

namespace App\Http\Controllers\Enrollment;

use App\Enrollee;
use App\Http\Controllers\Controller;
use App\Practice;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\Datatables\Facades\Datatables;

class EnrollmentConsentController extends Controller
{

    /**
     * @return mixed
     */
    public function index(){

        //todo change to Enrollee
        $enrollees = Enrollee::where('status', '!=', 'enrolled')->get();

        $formatted = [];
        $count = 0;

        foreach ($enrollees as $enrollee){

            $formatted[$count] = [

                'name'                     => $enrollee->first_name . ' ' . $enrollee->last_name,
                'program'                  => ucwords(Practice::find($enrollee->practice_id)->name),
                'provider'                 => ucwords(User::find($enrollee->provider_id)->fullName ?? null),
                'has_copay'                => $enrollee->has_copay ? 'Yes' : 'No',
                'status'                   => ucwords($enrollee->status),
                'care_ambassador'          => ucwords($enrollee->careAmbassador->user->fullName ?? null),
                'last_call_outcome'        => ucwords($enrollee->last_call_outcome),
                'last_call_outcome_reason' => ucwords($enrollee->last_call_outcome_reason),
                'mrn_number'               => ucwords($enrollee->mrn_number),
                'dob'                      => ucwords($enrollee->dob),
                'phone'                    => ucwords($enrollee->primary_phone),
                'attempt_count'            => ucwords($enrollee->attempt_count),
                'invite_sent_at'           => ucwords($enrollee->invite_sent_at),
                'invite_opened_at'         => ucwords($enrollee->invite_opened_at),
                'last_attempt_at'          => ucwords($enrollee->last_attempt_at),
                'consented_at'             => ucwords($enrollee->consented_at),

            ];
            $count++;

        }

        $formatted = collect($formatted);
        $formatted->sortByDesc('date');

        return Datatables::collection($formatted)->make(true);

    }

    public function makeEnrollmentReport()
    {

        return view('admin.reports.enrollment.enrollment-list');

    }

    public function create($invite_code){

        $enrollee = Enrollee::whereInviteCode($invite_code)->first();
        $enrollee->invite_opened_at = Carbon::now()->toDateTimeString();
        $enrollee->save();

        if(is_null($enrollee)){

            return view('errors.enrollmentConsentUrlError');

        }

        return view('enrollment-consent.create', ['enrollee' => $enrollee]);

    }

    public function store(Request $request){

        $input = $request->input();

        $enrollee = Enrollee::find($input['enrollee_id']);

        $enrollee->consented_at = Carbon::parse($input['consented_at'])->toDateTimeString();
        $enrollee->status = 'consented';
        $enrollee->save();

        return json_encode($enrollee);

    }

    public function update(Request $request){

        $input = $request->input();

        $enrollee = Enrollee::find($input['enrollee_id']);

        if(isset($input['days'])) {
            $enrollee->preferred_days = implode(', ', $input['days']);

        }

        if(isset($input['time'])) {

            $enrollee->preferred_window = $input['time'];
        }

        $enrollee->save();

        return view('enrollment-consent.thanks', ['enrollee' => $enrollee]);


    }

}
