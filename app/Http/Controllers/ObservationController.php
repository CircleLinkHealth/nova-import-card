<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Observation;
use App\Services\MsgCPRules;
use App\Services\ObservationService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Validator;

class ObservationController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
    }

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

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
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

    public function index(Request $request)
    {
        if ('ui' == $request->header('Client')) {
            $obs_id = Crypt::decrypt($request->header('obsId'));

            $wpUsers = (new User())->getObservation($obs_id);

            return response()->json(Crypt::encrypt(json_encode($wpUsers)));
        }
    }

    public function show($id)
    {
    }

    public function store(Request $request)
    {
        $observationService = new ObservationService();
        $msgCPRules         = new MsgCPRules();

        $input = $request->all();

        if (( ! $request->header('Client'))) {
            $wpUser = User::find($input['userId']);
            if ( ! $wpUser) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
            $validator = Validator::make($input, [
                'observationDate'   => 'required|date',
                'observationValue'  => 'required',
                'observationSource' => 'required',
            ]);
            if ($validator->fails()) {
                if ($request->header('Client')) {
                    return response()->json(['response' => 'Validation Error'], 500);
                }

                return redirect()->back()->withErrors($validator)->withInput();
            }

            //***** start extra work here to decode from quirky UI ******
            // creates params array to mimick the way mobi sends it
            $params['user_id'] = $wpUser->id;
            //$date = DateTime::createFromFormat("Y-m-d\TH:i", $input['observationDate']);
            $date = DateTime::createFromFormat('Y-m-d H:i', $input['observationDate']);

            //could not reproduce invalid date being sent here, but we found exception where Date could not be parsed (probably from an old IE browser)
            if ( ! $date) {
                if ($request->header('Client')) {
                    return response()->json(['response' => 'Validation Error'], 400);
                }

                return redirect()->back()->withErrors(['observationDate' => 'The date and/or time could not be parsed.'])->withInput();
            }

            $date = $date->format('Y-m-d H:i:s');

            if (isset($input['parent_id'])) {
                $params['parent_id'] = $input['parent_id'];
            }
            $params['parent_id']      = 0;
            $params['obs_value']      = str_replace('/', '_', $input['observationValue']);
            $params['obs_date']       = $date;
            $params['obs_message_id'] = $input['observationType'];
            $params['obs_key']        = ''; // need to get from obs_message_id
            $params['timezone']       = 'America/New_York';
            $params['qstype']         = '';
            $params['source']         = $input['observationSource'];
            $params['isStartingObs']  = 'N';
            if (isset($input['isStartingObs'])) {
                $params['isStartingObs'] = $input['isStartingObs'];
            }

            //***** end extra work here to decode from quirky UI ******
        }

        // process message id (ui dropdown includes qstype)
        $pieces = explode('/', $params['obs_message_id']);
        if (1 == count($pieces)) {
            // normal message, straight /messageId
            $obsMessageId = $params['obs_message_id'];
        } else {
            if (2 == count($pieces)) {
                // semi-normal message, qstype/messageId
                $qstype       = $pieces[0];
                $obsMessageId = $pieces[1];
            }
        }

        // validate answer
        $qsType         = $msgCPRules->getQsType($obsMessageId, '16');
        $answerResponse = $msgCPRules->getValidAnswer('16', $qsType, $obsMessageId, $params['obs_value'], false);

        //Hack to validate a1c.
        //This code is gross
        if ('RPT/CF_RPT_60' == $request->input('observationType')) {
            $params['obs_value'] = str_replace('%', '', $params['obs_value']);

            if (Str::contains(
                $params['obs_value'],
                '.'
            ) && 4 >= strlen($params['obs_value']) && is_numeric($params['obs_value'])
            ) {
                $answerResponse = true;
            }
        }

        if ( ! $answerResponse) {
            return redirect()->back()->withErrors(['You entered an invalid value, please review and resubmit.'])->withInput();
        }

        // validate timezone
        /*
        if(strlen($params['timezone']) < 5) {
            return response()->json(['response' => 'Error - Invalid timezone, please provide full timezone (not GMT offset)'], 500);
        }
        */

        $result = $observationService->storeObservationFromApp(
            $params['user_id'],
            $params['parent_id'],
            $params['obs_value'],
            $params['obs_date'],
            $obsMessageId,
            $params['obs_key'],
            $params['timezone'],
            $params['source'],
            $params['isStartingObs']
        );

        if ('mobi' == $request->header('Client') || 'ui' == $request->header('Client')) {
            // api response
            if ($result) {
                return response()->json(['response' => 'Observation Created'], 201);
            }

            return response()->json(['response' => 'Error'], 500);
        }
        // ui response
        return redirect()->route('patient.summary', [
            'patientId' => $wpUser->id,
            'programId' => $request->input('programId'),
        ])->with('messages', ['Successfully added new observation']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function update($id)
    {
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
