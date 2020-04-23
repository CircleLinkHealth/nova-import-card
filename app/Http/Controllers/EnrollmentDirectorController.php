<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\EnrolleeCustomFilter;
use App\EnrolleeView;
use App\Filters\EnrolleeFilters;
use App\Http\Requests\AddEnrolleeCustomFilter;
use App\Http\Requests\EditEnrolleeData;
use App\Http\Requests\UpdateMultipleEnrollees;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class EnrollmentDirectorController extends Controller
{
    public function addEnrolleeCustomFilter(AddEnrolleeCustomFilter $request)
    {
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

    public function assignCareAmbassadorToEnrollees(UpdateMultipleEnrollees $request)
    {
        Enrollee::whereIn('id', $request->input('enrolleeIds'))
            ->update([
                'status'                  => Enrollee::TO_CALL,
                'care_ambassador_user_id' => $request->input('ambassadorId'),
            ]);

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
        } else {
            //todo: fix this
            $data->orderByRaw("CASE
   WHEN status = 'call_queue' THEN 1
   WHEN status = 'utc' THEN 2
   ELSE 3
END ASC, attempt_count ASC");
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

    public function markEnrolleesAsIneligible(UpdateMultipleEnrollees $request)
    {
        Enrollee::whereIn('id', $request->input('enrolleeIds'))->update(['status' => Enrollee::INELIGIBLE]);

        return response()->json([], 200);
    }

    public function runCreateEnrolleesSeeder(Request $request)
    {
        if ($request->input('erase')) {
            Artisan::call('enrollees:erase-test');
            $message = 'Queued job to erase all demo patients. CareAmbassador Logs related to these patients will be reset. This may take a minute. Please refresh the page.';
        } else {
            Artisan::call('db:seed', ['--class' => 'EnrolleesSeeder']);
            $message = 'Created 10 Demo Patients. Please refresh the page.';
        }

        if ($request->input('redirect')) {
            return redirect()->back()->withErrors(['messages' => [$message]]);
        }

        return 'Test Patients have been created. Please close this window.';
    }

    public function unassignCareAmbassadorFromEnrollees(UpdateMultipleEnrollees $request)
    {
        Enrollee::whereIn('id', $request->input('enrolleeIds'))->update(['care_ambassador_user_id' => null]);

        return response()->json([], 200);
    }
}
