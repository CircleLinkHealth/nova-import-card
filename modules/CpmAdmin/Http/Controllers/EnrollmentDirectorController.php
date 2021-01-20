<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Controllers;

use Carbon\Carbon;
use CircleLinkHealth\CpmAdmin\Http\Requests\AddEnrolleeCustomFilter;
use CircleLinkHealth\CpmAdmin\Http\Requests\AssignCallbackToEnrollee;
use CircleLinkHealth\CpmAdmin\Http\Requests\EditEnrolleeData;
use CircleLinkHealth\CpmAdmin\Http\Requests\UpdateMultipleEnrollees;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Filters\EnrolleeCustomFilter;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Entities\EnrolleeView;
use CircleLinkHealth\SharedModels\Filters\EnrolleeFilters;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
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

    public function assignCallback(AssignCallbackToEnrollee $request)
    {
        $enrollee = Enrollee::findOrFail($request->input('enrollee_id'));

        $enrollee->update([
            'status'                  => Enrollee::TO_CALL,
            'care_ambassador_user_id' => $request->input('care_ambassador_user_id'),
            'requested_callback'      => Carbon::parse($request->input('callback_date')),
            'callback_note'           => htmlspecialchars($request->input('callback_note'), ENT_NOQUOTES),
        ]);

        return response()->json([], 200);
    }

    public function assignCareAmbassadorToEnrollees(UpdateMultipleEnrollees $request)
    {
        $careAmbassadorUser = User::with('careAmbassador')
            ->has('careAmbassador')
            ->findOrFail($request->input('ambassadorId'));

        $enrolleeIds = $request->input('enrolleeIds');

        $notAssigned = [];

        if ( ! $careAmbassadorUser->careAmbassador->speaks_spanish) {
            $spanishSpeakingEnrollees = Enrollee::whereIn('id', $enrolleeIds)
                ->where('lang', 'like', '%es%')
                ->orWhere('lang', 'like', '%sp%')
                ->pluck('id');

            foreach ($spanishSpeakingEnrollees as $id) {
                $key = array_search($id, $enrolleeIds);
                if (false !== $key) {
                    $notAssigned[] = $id;
                    unset($enrolleeIds[$key]);
                }
            }
        }

        $updated = Enrollee::whereIn('id', $enrolleeIds)
            ->update([
                'status'                  => Enrollee::TO_CALL,
                'care_ambassador_user_id' => $careAmbassadorUser->id,
            ]);

        $message                  = null;
        $unassignedEnrolleesExist = ! empty($notAssigned);
        if ($unassignedEnrolleesExist) {
            $ids     = implode(',', $notAssigned);
            $message = "The following patients have not been assigned to Care Ambassador ({$careAmbassadorUser->display_name}) because CA does not speak spanish: (IDs) {$ids}";
        }

        return response()->json([
            'enrollees_updated'    => $updated,
            'enrollee_ids_updated' => $enrolleeIds,
            'enrollees_unassigned' => ! empty($notAssigned),
            'message'              => $message,
        ], 200);
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
                'other_phone'         => $phones['other_phone'] ?? $phones['work_phone'] ?? null,
                'cell_phone'          => $phones['cell_phone'],
                'primary_insurance'   => $request->input('primary_insurance'),
                'secondary_insurance' => $request->input('secondary_insurance'),
                'tertiary_insurance'  => $request->input('tertiary_insurance'),
                'city'                => $request->input('city'),
                'state'               => $request->input('state'),
                'zip'                 => $request->input('zip'),
            ]);

        return response()->json([], 200);
    }

    public function getCareAmbassadors()
    {
        $ambassadors = User::ofType('care-ambassador')
            ->select(['id', 'display_name'])
            ->without(['roles', 'perms'])
            ->get()
            ->toArray();

        return response()->json($ambassadors);
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

        $now = Carbon::now()->toDateString();

        if (isset($orderBy)) {
            $direction = 1 == $ascending
                ? 'ASC'
                : 'DESC';
            $data->orderBy($orderBy, $direction);
        } else {
            $data->orderByRaw("CASE
            WHEN requested_callback IS NOT NULL AND DATE(requested_callback) <= DATE('{$now}') THEN 1
   WHEN status = 'call_queue' THEN 3
   WHEN status = 'utc' THEN 4
   ELSE 4
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
        return view('cpm-admin::admin.ca-director.index');
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

    public function searchEnrollables(Request $request)
    {
        $input = $request->input();

        if ( ! array_key_exists('enrollables', $input)) {
            return response()->json([], 400);
        }

        $searchTerms = explode(' ', $input['enrollables']);

        $query = Enrollee::with(['provider', 'practice']);

        foreach ($searchTerms as $term) {
            $query->where(function ($q) use ($term) {
                $q->where('id', $term)
                    ->orWhere('first_name', 'like', "%${term}%")
                    ->orWhere('last_name', 'like', "%${term}%");
            });
        }

        $enrollables = [];
        $query->chunk(100, function ($enrollees) use (&$enrollables) {
            foreach ($enrollees as $e) {
                $phonesString = "{$e->home_phone}, {$e->cell_phone}, {$e->other_phone}";

                $item = [
                    'id'       => $e->id,
                    'name'     => $e->first_name.' '.$e->last_name,
                    'mrn'      => $e->mrn,
                    'program'  => optional($e->practice)->display_name ?? '',
                    'provider' => optional($e->provider)->getFullName() ?? '',
                ];

                $item['hint'] = "{$item['name']} ({$item['id']}) PROVIDER: [{$item['provider']}] [{$item['program']}]  {$phonesString}";

                $enrollables[] = $item;
            }
        });

        return response()->json($enrollables);
    }

    public function unassignCareAmbassadorFromEnrollees(UpdateMultipleEnrollees $request)
    {
        Enrollee::whereIn('id', $request->input('enrolleeIds'))->update(['care_ambassador_user_id' => null]);

        return response()->json([], 200);
    }
}