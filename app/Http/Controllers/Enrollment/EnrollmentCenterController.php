<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment;

use App\CareAmbassadorLog;
use App\Enrollee;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EnrollmentCenterController extends Controller
{
    public function consented(Request $request)
    {
        $careAmbassador = auth()->user()->careAmbassador;

        $enrollee = Enrollee::find($request->input('enrollee_id'));

        //update report for care ambassador:
        $report                       = CareAmbassadorLog::createOrGetLogs($careAmbassador->id);
        $report->no_enrolled          = $report->no_enrolled + 1;
        $report->total_calls          = $report->total_calls + 1;
        $report->total_time_in_system = $request->input('total_time_in_system');
        $report->save();

        $enrollee->setHomePhoneAttribute($request->input('home_phone'));
        $enrollee->setCellPhoneAttribute($request->input('cell_phone'));
        $enrollee->setOtherPhoneAttribute($request->input('other_phone'));

        //set preferred phone
        switch ($request->input('preferred_phone')) {
            case 'home':
                $enrollee->setPrimaryPhoneNumberAttribute($request->input('home_phone'));
                break;
            case 'cell':
                $enrollee->setPrimaryPhoneNumberAttribute($request->input('cell_phone'));
                break;
            case 'other':
                $enrollee->setPrimaryPhoneNumberAttribute($request->input('other_phone'));
                break;
            default:
                $enrollee->setPrimaryPhoneNumberAttribute($request->input('home_phone'));
        }

        $enrollee->address                 = $request->input('address');
        $enrollee->address_2               = $request->input('address_2');
        $enrollee->state                   = $request->input('state');
        $enrollee->city                    = $request->input('city');
        $enrollee->zip                     = $request->input('zip');
        $enrollee->email                   = $request->input('email');
        $enrollee->dob                     = $request->input('dob');
        $enrollee->last_call_outcome       = $request->input('consented');
        $enrollee->care_ambassador_user_id = $careAmbassador->user_id;

        $enrollee->total_time_spent = $enrollee->total_time_spent + $request->input('time_elapsed');

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

        $enrollee->status          = 'consented';
        $enrollee->consented_at    = Carbon::now()->toDateTimeString();
        $enrollee->last_attempt_at = Carbon::now()->toDateTimeString();

        $enrollee->save();

        return redirect()->action('Enrollment\EnrollmentCenterController@dashboard');
    }

    public function dashboard()
    {
        $careAmbassador = auth()->user()->careAmbassador;

        if ( ! $careAmbassador) {
            return view('errors.403', [
                'message'   => 'You need to be a Care Ambassador to acccess this page.',
                'hideLinks' => true,
            ]);
        }

        //if logged in ambassador is spanish, pick up a spanish patient
        if ($careAmbassador->speaks_spanish) {
            $enrollee = Enrollee::where('care_ambassador_user_id', $careAmbassador->user_id)
                ->toCall()
                ->where('lang', 'ES')
                ->orderBy('attempt_count')
                ->with('practice.enrollmentTips')
                ->first();

            //if no spanish, get a EN user.
            if (null == $enrollee) {
                $enrollee = Enrollee::where('care_ambassador_user_id', $careAmbassador->user_id)
                    ->toCall()
                    ->orderBy('attempt_count')
                    ->with('practice.enrollmentTips')
                    ->first();
            }
        } else { // auth ambassador doesn't speak ES, get a regular user.
            $enrollee = Enrollee::where('care_ambassador_user_id', $careAmbassador->user_id)
                ->toCall()
                ->orderBy('attempt_count')
                ->with('practice.enrollmentTips')
                ->first();
        }

        $engagedEnrollee = Enrollee::where('care_ambassador_user_id', $careAmbassador->user_id)
            ->where('status', '=', 'engaged')
            ->orderBy('attempt_count')
            ->with('practice.enrollmentTips')
            ->first();

        if ($engagedEnrollee) {
            $enrollee = $engagedEnrollee;
        }

        if (null == $enrollee) {
            //no calls available
            return view('enrollment-ui.no-available-calls');
        }

        //mark as engaged to prevent double dipping
        $enrollee->status = 'engaged';
        $enrollee->save();

        return view(
            'enrollment-ui.dashboard',
            [
                'enrollee' => $enrollee,
                'report'   => CareAmbassadorLog::createOrGetLogs($careAmbassador->id),
            ]
        );
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function rejected(Request $request)
    {
        $enrollee       = Enrollee::find($request->input('enrollee_id'));
        $careAmbassador = auth()->user()->careAmbassador;

        //soft_rejected or rejected
        $status = $request->input('status', Enrollee::REJECTED);

        //update report for care ambassador:
        $report = CareAmbassadorLog::createOrGetLogs($careAmbassador->id);

        if (Enrollee::REJECTED === $status) {
            $report->no_rejected = $report->no_rejected + 1;
        } else {
            $report->no_soft_rejected = $report->no_soft_rejected + 1;
        }

        $report->total_calls          = $report->total_calls + 1;
        $report->total_time_in_system = $request->input('total_time_in_system');
        $report->save();

        $enrollee->last_call_outcome = $request->input('reason');

        if ($request->input('reason_other')) {
            $enrollee->last_call_outcome_reason = $request->input('reason_other');
        }

        $enrollee->care_ambassador_user_id = $careAmbassador->user_id;

        $enrollee->status = $status;
        if ($request->has('soft_decline_callback')) {
            $enrollee->soft_rejected_callback = $request->input('soft_decline_callback');
        }

        $enrollee->attempt_count    = $enrollee->attempt_count + 1;
        $enrollee->last_attempt_at  = Carbon::now()->toDateTimeString();
        $enrollee->total_time_spent = $enrollee->total_time_spent + $request->input('time_elapsed');

        $enrollee->save();

        return redirect()->action('Enrollment\EnrollmentCenterController@dashboard');
    }

    public function training()
    {
        return view('enrollment-ui.training');
    }

    public function unableToContact(Request $request)
    {
        $enrollee       = Enrollee::find($request->input('enrollee_id'));
        $careAmbassador = auth()->user()->careAmbassador;

        //update report for care ambassador:
        $report                       = CareAmbassadorLog::createOrGetLogs($careAmbassador->id);
        $report->no_utc               = $report->no_utc + 1;
        $report->total_calls          = $report->total_calls + 1;
        $report->total_time_in_system = $request->input('total_time_in_system');
        $report->save();

        $enrollee->last_call_outcome = $request->input('reason');

        if ($request->input('reason_other')) {
            $enrollee->last_call_outcome_reason = $request->input('reason_other');
        }

        $enrollee->care_ambassador_user_id = $careAmbassador->user_id;

        if ('requested callback' == $request->input('reason')) {
            $enrollee->status = 'call_queue';
        } else {
            $enrollee->status = 'utc';
        }

        $enrollee->attempt_count    = $enrollee->attempt_count + 1;
        $enrollee->last_attempt_at  = Carbon::now()->toDateTimeString();
        $enrollee->total_time_spent = $enrollee->total_time_spent + $request->input('time_elapsed');

        $enrollee->save();

        return redirect()->action('Enrollment\EnrollmentCenterController@dashboard');
    }
}
