<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Enrollee;
use App\EnrolleeCustomFilter;
use App\EnrolleeView;
use App\Filters\EnrolleeFilters;
use App\Http\Requests\EditEnrolleeData;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;

class EnrollmentDirectorController extends Controller
{
    public function addEnrolleeCustomFilter(Request $request)
    {
        if (empty($request['practice_id']['value'])) {
            return response()->json([
                'errors' => 'Please select Practice or all Practices',
            ], 400);
        }

        if (empty($request['filter_type']['value'])) {
            return response()->json([
                'errors' => 'Please select a filter type',
            ], 400);
        }

        if (empty($request['filter_name'])) {
            return response()->json([
                'errors' => 'Please type in the name of the filter you would like to add.',
            ], 400);
        }

        $customFilter = EnrolleeCustomFilter::updateOrCreate([
            'name' => strtolower($request['filter_name']),
            'type' => $request['filter_type']['value'],
        ]);

        if ('all' == $request['practice_id']['value']) {
            $practices = Practice::active()->get();
            $practices->map(function ($p) use ($customFilter) {
                $p->enrolleeCustomFilters()->attach($customFilter->id, ['include' => 1]);
            });
        } else {
            $practice = Practice::find($request['practice_id']['value']);

            $practice->enrolleeCustomFilters()->attach($customFilter->id, ['include' => 1]);
        }

        return response()->json([], 200);
    }

    public function assignCareAmbassadorToEnrollees(Request $request)
    {
        if ( ! $request->input('enrolleeIds')) {
            return response()->json([
                'errors' => 'No enrollee Ids were sent in the request',
            ], 400);
        }
        if ( ! $request->input('ambassadorId')) {
            return response()->json([
                'errors' => 'No ambassador Id was sent in the request',
            ], 400);
        }

        Enrollee::whereIn(
            'id',
            $request->input('enrolleeIds')
        )
            ->get()
            ->map(function ($e) use ($request) {
                    $e->care_ambassador_user_id = $request->input('ambassadorId');

                    if (Enrollee::SOFT_REJECTED != $e->status) {
                        $e->status = Enrollee::TO_CALL;
                    }
                    $e->save();
                });

        return response()->json([], 200);
    }

    public function editEnrolleeData(EditEnrolleeData $request)
    {
        $phones = $request->input('phones');

        Enrollee::where('id', $request->input('id'))
            ->update([
                'first_name'          => $request->input('first_name'),
                'last_name'           => $request->input('last_name'),
                'lang'                => $request->input('lang'),
                'status'              => $request->input('status'),
                'address'             => $request->input('address'),
                'address_2'           => $request->input('address_2'),
                'primary_phone'       => $phones['primary_phone'],
                'home_phone'          => $phones['home_phone'],
                'other_phone'         => $phones['other_phone'],
                'cell_phone'          => $phones['cell_phone'],
                'primary_insurance'   => $request->input('primary_insurance'),
                'secondary_insurance' => $request->input('secondary_insurance'),
                'tertiary_insurance'  => $request->input('tertiary_insurance'),
            ]);

        return response()->json([], 200);
    }

    public function getCareAmbassadors()
    {
        $ambassadors = User::ofType('care-ambassador')
            ->select(['id', 'display_name'])
            ->get();

        return response()->json($ambassadors->toArray());
    }

    public function getEnrollees(Request $request, EnrolleeFilters $filters)
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

        if (isset($orderBy)) {
            $direction = 1 == $ascending
                ? 'ASC'
                : 'DESC';
            $data->orderBy($orderBy, $direction);
        }

        $results = $data->get()->toArray();

        return [
            'data'  => $results,
            'count' => $count,
        ];
    }

    public function index()
    {
        return view('admin.ca-director.index');
    }

    public function markEnrolleesAsIneligible(Request $request)
    {
        if ( ! $request->input('enrolleeIds')) {
            return response()->json([
            ], 400);
        }

        Enrollee::whereIn('id', $request->input('enrolleeIds'))->update(['status' => Enrollee::INELIGIBLE]);

        return response()->json([], 200);
    }

    public function unassignCareAmbassadorFromEnrollees(Request $request)
    {
        if ( ! $request->input('enrolleeIds')) {
            return response()->json([
            ], 400);
        }

        Enrollee::whereIn('id', $request->input('enrolleeIds'))->update(['care_ambassador_user_id' => null]);

        return response()->json([], 200);
    }
}
