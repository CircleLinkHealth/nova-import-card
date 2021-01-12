<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Controllers;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Observation;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;

class ObservationController extends Controller
{
    public function dashboardIndex()
    {
        return view('cpm-admin::admin.observations.dashboard.index');
    }

    public function deleteObservation(Request $request)
    {
        $observation = Observation::find($request['obsId']);

        $userId = $observation->user_id;

        $observation->delete();

        return redirect()->route('observations-dashboard.list', ['userId' => $userId])->with(
            'msg',
            'Observation Successfully Deleted.'
        );
    }

    public function editObservation(Request $request)
    {
        $obsId = $request['obsId'];

        $observation = Observation::with(['user', 'comment'])
            ->where('id', $obsId)
            ->first();

        return view('cpm-admin::admin.observations.dashboard.edit', compact('observation'));
    }

    public function getObservationsList(Request $request)
    {
        $user = User::find($request['userId']);

        if ( ! $user) {
            return redirect()->route('observations-dashboard.index')->with('msg', 'User not found.');
        }

        $observations = Observation::where('user_id', $user->id)->get();

        $observations             = collect($observations);
        $currentPage              = LengthAwarePaginator::resolveCurrentPage();
        $perPage                  = 10;
        $currentPageSearchResults = $observations->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $observations             = new LengthAwarePaginator($currentPageSearchResults, count($observations), $perPage);

        $observations = $observations->withPath('admin/observations-dashboard/list');

        return view('cpm-admin::admin.observations.dashboard.index', compact(['user', 'observations']));
    }

    public function updateObservation(Request $request)
    {
        $observation = Observation::find($request['obsId']);

        $key       = $request['obs_key'];
        $value     = $request['obs_value'];
        $method    = $request['obs_method'];
        $messageId = $request['obs_message_id'];
        //ask about date TODO
        $date = new Carbon($request['date']);

        if ($observation->obs_key == $key &&
            $observation->obs_value == $value &&
            $observation->obs_method == $method &&
            $observation->obs_message_id == $messageId) {
            return redirect()->route('observations-dashboard.edit', ['obsId' => $observation->id])->with(
                'msg',
                'No changes have been made.'
            );
        }

        $observation->obs_key        = $key;
        $observation->obs_value      = $value;
        $observation->obs_method     = $method;
        $observation->obs_message_id = $messageId;
        $observation->save();

        return redirect()->route('observations-dashboard.edit', ['obsId' => $observation->id])->with(
            'msg',
            'Changes Successfully Applied.'
        );
    }

    private function saveObservationAndRedirect(Observation $newObservation)
    {
        $newObservation->save();

        return redirect()->route('patient.summary', [
            'patientId' => $newObservation->user_id,
        ])->with('messages', ['Successfully added new observation']);
    }
}
