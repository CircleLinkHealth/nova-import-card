<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Observation;
use App\User;
use Illuminate\Http\Request;

class ObservationController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        // display view
        return view('observations.create', []);
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
        Observation::destroy($id);

        return redirect()->back()->with('messages', ['successfully removed observation']);
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
        $observation = Observation::find($id);

        return view('admin.observations.edit', ['observation' => $observation, 'messages' => \Session::get('messages')]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        // display view
        $observations = Observation::OrderBy('id', 'desc')->limit('100');
        $users        = User::OrderBy('id', 'desc')->limit('100')->get();

        // FILTERS
        $params = $request->all();

        // filter user
        $users      = User::OrderBy('id', 'desc')->get()->pluck('fullNameWithId', 'id')->all();
        $filterUser = 'all';
        if (!empty($params['filterUser'])) {
            $filterUser = $params['filterUser'];
            if ('all' != $params['filterUser']) {
                $observations = $observations->whereHas('user', function ($q) use ($filterUser) {
                    $q->where('id', '=', $filterUser);
                });
            }
        }

        // filter key
        $obsKeys      = ['Severity' => 'Severity', 'Other' => 'Other', 'Blood_Pressure' => 'Blood_Pressure', 'Blood_Sugar' => 'Blood_Sugar', 'Cigarettes' => 'Cigarettes', 'Weight' => 'Weight', 'Adherence' => 'Adherence'];
        $filterObsKey = 'all';
        if (!empty($params['filterObsKey'])) {
            $filterObsKey = $params['filterObsKey'];
            if ('all' != $params['filterObsKey']) {
                $observations = $observations->where('obs_key', '=', $filterObsKey);
            }
        }
        $observations = $observations->paginate(10);

        return view('admin.observations.index', ['observations' => $observations, 'users' => $users, 'filterUser' => $filterUser, 'obsKeys' => $obsKeys, 'filterObsKey' => $filterObsKey]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        // display view
        $observation = Observation::find($id);

        return view('admin.observations.show', ['observation' => $observation, 'errors' => [], 'messages' => []]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $params                   = $request->input();
        $observation              = new Observation();
        $observation->msg_id      = $params['msg_id'];
        $observation->qtype       = $params['qtype'];
        $observation->obs_key     = $params['obs_key'];
        $observation->description = $params['description'];
        $observation->icon        = $params['icon'];
        $observation->category    = $params['category'];
        $observation->save();

        return redirect()->route('admin.observations.edit', [$observation->qid])->with(
            'messages',
            ['successfully added new observation - '.$params['msg_id']]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $params                   = $request->input();
        $observation              = Observation::find($id);
        $observation->msg_id      = $params['msg_id'];
        $observation->qtype       = $params['qtype'];
        $observation->obs_key     = $params['obs_key'];
        $observation->description = $params['description'];
        $observation->icon        = $params['icon'];
        $observation->category    = $params['category'];
        $observation->save();

        return redirect()->back()->with('messages', ['successfully updated observation']);
    }
}
