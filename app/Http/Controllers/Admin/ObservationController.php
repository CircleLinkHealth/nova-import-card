<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Observation;
use App\User;
use Auth;
use Illuminate\Http\Request;

class ObservationController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        if (!Auth::user()->can('observations-view')) {
            abort(403);
        }
        // display view
        $observations = Observation::OrderBy('id', 'desc')->limit('100');
        $users = User::OrderBy('id', 'desc')->limit('100')->get();

        // FILTERS
        $params = $request->all();

        // filter user
        $users = User::OrderBy('id', 'desc')->get()->pluck('fullNameWithId', 'id')->all();
        $filterUser = 'all';
        if (!empty($params['filterUser'])) {
            $filterUser = $params['filterUser'];
            if ($params['filterUser'] != 'all') {
                $observations = $observations->whereHas('user', function ($q) use ($filterUser) {
                    $q->where('id', '=', $filterUser);
                });
            }
        }

        // filter key
        $obsKeys = ['Severity' => 'Severity', 'Other' => 'Other', 'Blood_Pressure' => 'Blood_Pressure', 'Blood_Sugar' => 'Blood_Sugar', 'Cigarettes' => 'Cigarettes', 'Weight' => 'Weight', 'Adherence' => 'Adherence'];
        $filterObsKey = 'all';
        if (!empty($params['filterObsKey'])) {
            $filterObsKey = $params['filterObsKey'];
            if ($params['filterObsKey'] != 'all') {
                $observations = $observations->where('obs_key', '=', $filterObsKey);
            }
        }
        $observations = $observations->paginate(10);

        return view('admin.observations.index', [ 'observations' => $observations, 'users' => $users, 'filterUser' => $filterUser, 'obsKeys' => $obsKeys, 'filterObsKey' => $filterObsKey ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        if (!Auth::user()->can('observations-create')) {
            abort(403);
        }
        // display view
        return view('admin.observations.create', []);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        if (!Auth::user()->can('observations-create')) {
            abort(403);
        }
        $params = $request->input();
        $observation = new Observation;
        $observation->msg_id = $params['msg_id'];
        $observation->qtype = $params['qtype'];
        $observation->obs_key = $params['obs_key'];
        $observation->description = $params['description'];
        $observation->icon = $params['icon'];
        $observation->category = $params['category'];
        $observation->save();
        return redirect()->route('admin.observations.edit', [$observation->qid])->with('messages', ['successfully added new observation - '.$params['msg_id']])->send();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        if (!Auth::user()->can('observations-view')) {
            abort(403);
        }
        // display view
        $observation = Observation::find($id);
        return view('admin.observations.show', [ 'observation' => $observation, 'errors' => [], 'messages' => [] ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        if (!Auth::user()->can('observations-edit')) {
            abort(403);
        }
        $observation = Observation::find($id);
        return view('admin.observations.edit', [ 'observation' => $observation, 'messages' => \Session::get('messages') ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        if (!Auth::user()->can('observations-edit')) {
            abort(403);
        }
        $params = $request->input();
        $observation = Observation::find($id);
        $observation->msg_id = $params['msg_id'];
        $observation->qtype = $params['qtype'];
        $observation->obs_key = $params['obs_key'];
        $observation->description = $params['description'];
        $observation->icon = $params['icon'];
        $observation->category = $params['category'];
        $observation->save();
        return redirect()->back()->with('messages', ['successfully updated observation'])->send();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        if (!Auth::user()->can('observations-destroy')) {
            abort(403);
        }
        Observation::destroy($id);
        return redirect()->back()->with('messages', ['successfully removed observation'])->send();
    }
}
