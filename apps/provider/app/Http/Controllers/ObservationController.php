<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Observation;
use App\Services\Observations\ObservationConstants;
use App\Services\Observations\ObservationService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
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
            'obs_date'       => Carbon::parse($request->input('observationDate'))->format('Y-m-d H:i:s'),
            'sequence_id'    => 0,
            'obs_message_id' => $request->input('observationType'),
            'obs_method'     => $request->input('observationSource'),
            'user_id'        => $request->input('userId'),
            'obs_value'      => $request->input('observationValue'),
            'obs_key'        => ObservationService::getObsKey($request->input('observationType')),
        ]);

        //@todo: define BooleanObservation class
        if (in_array($newObservation->obs_key, [ObservationConstants::MEDICATIONS_ADHERENCE_OBSERVATION_TYPE, ObservationConstants::LIFESTYLE_OBSERVATION_TYPE])) {
            if ( ! in_array(strtoupper($newObservation->obs_value), ['Y', 'N', 'YES', 'NO'])) {
                return redirect()->back()->withErrors(["\"$newObservation->obs_value\" is not an accepted value for $newObservation->obs_key observations. Please enter a Y or a N."])->withInput();
            }

            $newObservation->obs_value = strtoupper($newObservation->obs_value[0]);

            return $this->saveObservationAndRedirect($newObservation);
        }

        //@todo: define NumericRangeObservation class
        if (in_array($newObservation->obs_key, [ObservationConstants::SYMPTOMS_OBSERVATION_TYPE])) {
            $validator = Validator::make([$newObservation->obs_key => $newObservation->obs_value], [
                $newObservation->obs_key => 'required|numeric|between:1,9',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors(["\"$newObservation->obs_value\" is not an accepted value for $newObservation->obs_key observations. Please enter a number from 1 to 9."])->withInput();
            }

            return $this->saveObservationAndRedirect($newObservation);
        }

        //@todo: define NumericRangeObservation class
        if (ObservationConstants::CIGARETTE_COUNT === $newObservation->obs_key) {
            $validator = Validator::make([$newObservation->obs_key => $newObservation->obs_value], [
                $newObservation->obs_key => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors(["\"$newObservation->obs_value\" is not an accepted value for $newObservation->obs_key observations. Please enter a number greater than, or equal to 0."])->withInput();
            }

            return $this->saveObservationAndRedirect($newObservation);
        }

        //@todo: define NumericRangeObservation Or Percentage class
        if (ObservationConstants::A1C === $newObservation->obs_key) {
            $newObservation->obs_value = str_replace('%', '', $newObservation->obs_value);

            $validator = Validator::make([$newObservation->obs_key => $newObservation->obs_value], [
                $newObservation->obs_key => 'required|numeric|between:0.001,100',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors(["\"$newObservation->obs_value\" is not an accepted value for $newObservation->obs_key observations. Please enter decimal percentage such as \"5.0%\", or \"6.12%\"."])->withInput();
            }

            return $this->saveObservationAndRedirect($newObservation);
        }

        if (ObservationConstants::BLOOD_SUGAR === $newObservation->obs_key) {
            $validator = Validator::make([$newObservation->obs_key => $newObservation->obs_value], [
                $newObservation->obs_key => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors(["\"$newObservation->obs_value\" is not an accepted value for $newObservation->obs_key observations. Please enter a number greater than, or equal to 1."])->withInput();
            }

            return $this->saveObservationAndRedirect($newObservation);
        }

        if (ObservationConstants::WEIGHT === $newObservation->obs_key) {
            $validator = Validator::make([$newObservation->obs_key => $newObservation->obs_value], [
                $newObservation->obs_key => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors(["\"$newObservation->obs_value\" is not an accepted value for $newObservation->obs_key observations. Please enter a number greater than, or equal to 1."])->withInput();
            }

            return $this->saveObservationAndRedirect($newObservation);
        }

        if (ObservationConstants::BLOOD_PRESSURE === $newObservation->obs_key) {
            $validator = Validator::make([$newObservation->obs_key => $newObservation->obs_value], [
                $newObservation->obs_key => [
                    'required',
                    function ($attribute, $value, $fail) {
                        $pieces = explode('/', $value);
                        if (2 !== count($pieces)
                            || ! ctype_digit($pieces[0])
                            || (int) $pieces[0] < 1
                            || ! ctype_digit($pieces[1])
                            || (int) $pieces[1] < 1
                        ) {
                            $fail("$value is not a valid Blood Pressure. An example of a valid Blood Pressure is 120/80");
                        }
                    }, ],
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors(["\"$newObservation->obs_value\" is not an accepted value for $newObservation->obs_key observations. Please enter a number greater than 0."])->withInput();
            }

            return $this->saveObservationAndRedirect($newObservation);
        }

        return redirect()->back()->withErrors(['The Observation could not be processed.'])->withInput();
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
