<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Observation;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Validator;

class ObservationController extends Controller
{
    public function dashboardIndex()
    {
        return view('admin.observations.dashboard.index');
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

        return view('admin.observations.dashboard.edit', compact('observation'));
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

        return view('admin.observations.dashboard.index', compact(['user', 'observations']));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'observationDate'   => 'required|date',
            'observationValue'  => 'required',
            'observationSource' => 'required',
            'userId'            => ['required', function ($attribute, $value, $fail) {
                if ( ! User::where('id', $value)->ofType('participant')->has('patientInfo')->exists()) {
                    $fail('Invalid Patient.');
                }
            }],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $newObservation = new Observation([
            'obs_date'       => Carbon::createFromFormat('Y-m-d H:i', $request->input('observationDate'))->format('Y-m-d H:i:s'),
            'sequence_id'    => 0,
            'obs_message_id' => $request->input('observationType'),
            'obs_method'     => $request->input('observationSource'),
            'user_id'        => $request->input('userId'),
            'obs_value'      => $request->input('observationValue'),
            'obs_key'        => '',
            'obs_unit'       => '',
        ]);

        $answerResponse = false;

        if ('CF_RPT_60' == $request->input('observationType')) {
            $newObservation->obs_value = str_replace('%', '', $newObservation->obs_value);

            if (Str::contains(
                $newObservation->obs_value,
                '.'
            ) && 4 >= strlen($newObservation->obs_value)
                && is_numeric($newObservation->obs_value)
            ) {
                $answerResponse = true;
            }
        }

        if ('CF_RPT_20' == $request->input('observationType')) {
            $newObservation->obs_value = str_replace('%', '', $newObservation->obs_value);

            if (Str::contains(
                $newObservation->obs_value,
                '.'
            ) && 3 == strlen($newObservation->obs_value) && is_numeric($newObservation->obs_value)
            ) {
                $answerResponse = true;
            }
        }

        if ( ! $answerResponse) {
            return redirect()->back()->withErrors(['You entered an invalid value, please review and resubmit.'])->withInput();
        }

        $newObservation->save();

        return redirect()->route('patient.summary', [
            'patientId' => $request->input('userId'),
            'programId' => $request->input('programId'),
        ])->with('messages', ['Successfully added new observation']);
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
}
