<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment;

use App\EnrolleeView;
use App\Filters\EnrolleeFilters;
use App\Http\Controllers\Controller;
use App\Http\Resources\EnrolleeCsvResource;
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
    public function index(Request $request, EnrolleeFilters $filters)
    {
        $fields = ['*'];

        $byColumn  = $request->get('byColumn');
        $query     = $request->get('query');
        $limit     = $request->get('limit');
        $orderBy   = $request->get('orderBy');
        $ascending = $request->get('ascending');
        $page      = $request->get('page');

        $data = EnrolleeView::filter($filters)->select($fields);

        $count = $data->count();

        $data->limit($limit)
            ->skip($limit * ($page - 1));

        $now = Carbon::now()->toDateString();

        if (isset($orderBy)) {
            $direction = 1 == $ascending
                ? 'ASC'
                : 'DESC';
            $data->orderBy($orderBy, $direction);
        }

        $filtersInput = $filters->filters();

        if ($filters->isCsv()) {
            return EnrolleeCsvResource::collection($data->paginate($filtersInput['rows']));
        }

        $results = $data->get()->toArray();

        return [
            'data'  => $results,
            'count' => $count,
        ];
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
