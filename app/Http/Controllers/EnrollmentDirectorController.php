<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\CareAmbassadorLog;
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
            $data->orderByRaw("CASE
   WHEN status = 'engaged' THEN 1
   WHEN status = 'call_queue' THEN 2
   WHEN status = 'utc' THEN 3
   WHEN status = 'soft_rejected' THEN 4
   ELSE 5
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
            $this->eraseTestEnrollees();
            $message = 'All demo patients erased. CareAmbassador Logs related to these patients have been reset.';
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

    /**
     * Erase test enrollees (created by seeder - having is_demo set as true in eligibilityJob->data)
     * And all potential related data that might be generated during the testing phase, including users created.
     * Also, reset CareAmbassador Logs for CA's that have called these patients.
     */
    private function eraseTestEnrollees()
    {
        $enrollees = Enrollee::whereHas('eligibilityJob', function ($j) {
            //only check for this. These are only seeder enrollees.
            $j->where('data->is_demo', 'true');
        })
            ->get();

        foreach ($enrollees as $enrollee) {
            //erase eligibility job
            $enrollee->eligibilityJob()->delete();

            //erase ccda data
            $imr = $enrollee->getImportedMedicalRecord();
            if ($imr) {
                $ccda = $imr->medicalRecord();
                if ($ccda) {
                    $ccda->forceDelete();
                }
                $imr->forceDelete();
            }

            //erase user and data
            $user = $enrollee->user()->first();

            if ($user) {
                $user->patientSummaries()->delete();
                $user->forceDelete();
            }

            $careAmbassador = $enrollee->careAmbassador()->first();

            if ($careAmbassador) {
                $date = $enrollee->updated_at->format('Y-m-d');
                CareAmbassadorLog::where('enroller_id', $careAmbassador->careAmbassador->id)
                    ->where('day', $date)
                    ->delete();
            }

            $enrollee->delete();
        }
    }
}
