<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment;

use App\EnrolleeView;
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
        $enrollees = EnrolleeView::
            whereNotIn('status', [Enrollee::ENROLLED, Enrollee::LEGACY, Enrollee::INELIGIBLE])
                ->where(function ($subQuery) {
                    $subQuery->whereNull('attempt_count')
                        ->orWhere('attempt_count', '<=', 3);
                })
                ->get();

        return datatables()->collection($enrollees->toArray())->make(true);
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
