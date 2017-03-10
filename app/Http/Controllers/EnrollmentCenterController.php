<?php

namespace App\Http\Controllers;

use App\CareAmbassadorLog;
use App\Enrollee;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EnrollmentCenterController extends Controller
{

    public function dashboard()
    {

        //get an eligible patient.
        $enrollee = Enrollee::toCall()->first();

        if ($enrollee == null) {

            //no calls available
            return view('enrollment-ui.no-available-calls');

        }

        //mark as engaged to prevent double dipping
        $enrollee->status = 'engaged';
        $enrollee->save();

        return view('enrollment-ui.dashboard',
            [
                'enrollee' => $enrollee,
                'report'   => CareAmbassadorLog::createOrGetLogs(auth()->user()->id),

            ]
        );

    }

    public function consented(Request $request)
    {

        $enrollee = Enrollee::find($request->input('enrollee_id'));

        //update report for care ambassador:
        $report = CareAmbassadorLog::createOrGetLogs(auth()->user()->id);
        $report->no_enrolled = $report->no_enrolled + 1;
        $report->total_calls = $report->total_calls + 1;
        $report->total_time_in_system = $request->input('time_elapsed');
        $report->save();

        $enrollee->setHomePhoneAttribute($request->input('home_phone'));
        $enrollee->setPrimaryPhoneNumberAttribute($request->input('primary_phone'));
        $enrollee->setCellPhoneAttribute($request->input('cell_phone'));
        $enrollee->setOtherPhoneAttribute($request->input('other_phone'));

        $enrollee->address = $request->input('address');
        $enrollee->address_2 = $request->input('address_2');
        $enrollee->state = $request->input('state');
        $enrollee->city = $request->input('city');
        $enrollee->zip = $request->input('zip');
        $enrollee->email = $request->input('email');
        $enrollee->dob = $request->input('dob');
        $enrollee->last_call_outcome = $request->input('consented');
        $enrollee->care_ambassador_id = auth()->user()->id;

        $enrollee->attempt_count = $enrollee->attempt_count + 1;

        if ($request->input('extra')) {
            $enrollee->last_call_outcome_reason = $request->input('extra');
        }

        if (is_array($request->input('days'))) {
            $enrollee->preferred_days = implode(', ', $request->input('days'));

        }

        if (is_array($request->input('times'))) {

            $enrollee->preferred_window = implode(', ', $request->input('times'));
        }

        $enrollee->status = 'consented';
        $enrollee->consented_at = Carbon::now()->toDateTimeString();
        $enrollee->last_attempt_at = Carbon::now()->toDateTimeString();

        $enrollee->save();

        return redirect()->action('EnrollmentCenterController@dashboard');

    }

    public function unableToContact(Request $request)
    {

        $enrollee = Enrollee::find($request->input('enrollee_id'));

        //update report for care ambassador:
        $report = CareAmbassadorLog::createOrGetLogs(auth()->user()->id);
        $report->no_utc = $report->no_utc + 1;
        $report->total_calls = $report->total_calls + 1;
        $report->total_time_in_system = $request->input('time_elapsed');
        $report->save();

        $enrollee->last_call_outcome = $request->input('reason');

        if ($request->input('reason_other')) {
            $enrollee->last_call_outcome_reason = $request->input('reason_other');
        }

        $enrollee->care_ambassador_id = auth()->user()->id;

        $enrollee->status = 'utc';
        $enrollee->attempt_count = $enrollee->attempt_count + 1;
        $enrollee->last_attempt_at = Carbon::now()->toDateTimeString();

        $enrollee->save();


        return redirect()->action('EnrollmentCenterController@dashboard');

    }

    public function rejected(Request $request)
    {

        $enrollee = Enrollee::find($request->input('enrollee_id'));

        //update report for care ambassador:
        $report = CareAmbassadorLog::createOrGetLogs(auth()->user()->id);
        $report->no_rejected = $report->no_rejected + 1;
        $report->total_calls = $report->total_calls + 1;
        $report->total_time_in_system = $request->input('time_elapsed');
        $report->save();


        $enrollee->last_call_outcome = $request->input('reason');

        if ($request->input('reason_other')) {
            $enrollee->last_call_outcome_reason = $request->input('reason_other');
        }

        $enrollee->care_ambassador_id = auth()->user()->id;

        $enrollee->status = 'rejected';
        $enrollee->attempt_count = $enrollee->attempt_count + 1;
        $enrollee->last_attempt_at = Carbon::now()->toDateTimeString();

        $enrollee->save();

        return redirect()->action('EnrollmentCenterController@dashboard');

    }

    public function training()
    {

        return view('enrollment-ui.training');

    }

}
