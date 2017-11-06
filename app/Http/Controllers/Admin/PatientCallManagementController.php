<?php namespace App\Http\Controllers\Admin;

use App\Call;
use App\Http\Controllers\Controller;
use App\User;
use Auth;
use DateTime;
use Illuminate\Http\Request;

class PatientCallManagementController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {

        if ($request->input('action')) {
            //Manage Assignments
            if ($request->input('action') == 'assign') {
                if (($request->input('calls')
                        && !empty($request->input('calls')))
                    && ($request->input('assigned_nurse')
                        && !empty($request->input('assigned_nurse')))
                ) {
                    if (is_array($request->input('calls'))) {
                        foreach ($request->input('calls') as $callId) {
                            $call = Call::find($callId);
                            if ($call) {
                                if ($request->input('assigned_nurse') && !empty($request->input('assigned_nurse')) && $request->input('assigned_nurse') != 'unassigned') {
                                    $call->outbound_cpm_id = $request->input('assigned_nurse');
                                } else {
                                    $call->outbound_cpm_id = null;
                                }
                                $call->save();
                            }
                        }
                    }

                    // assign nurse to calls
                    return redirect()->back()->with('messages', ['Successfully assigned calls to nurse!']);
                }
            }

            if ($request->input('action') == 'delete' && !empty($request->input('calls'))) {
                if (is_array($request->input('calls'))) {
                    foreach ($request->input('calls') as $callId) {
                        $call = Call::find($callId);

                        if ($call) {
                            $call->delete();
                        }
                    }
                }

                return redirect()->back()->with('messages', ['Successfully deleted calls!']);
            }
        }

        // get all calls
        $calls = Call::where('scheduled_date', '!=', '0000-00-00');

        // filter date
        //$date = new DateTime(date('Y-m-d'));
        $date = 'All';
        if ($request->input('date')) {
            if (strtolower($request->input('date')) != 'all') {
                $date = new DateTime($request->input('date') . ' 00:00:01');
                $calls->where('scheduled_date', '=', $date->format('Y-m-d'));
                $date = $date->format('Y-m-d');
            }
        }

        // filter nurse
        $filterNurse = [];
        if (!empty($request->input('filterNurse'))) {
            $filterNurse = $request->input('filterNurse');
            if ($request->input('filterNurse') == 'unassigned') {
                $calls->where('outbound_cpm_id', '=', null);
            } else {
                if ($request->input('filterNurse') != 'all') {
                    $calls->where('outbound_cpm_id', '=', $filterNurse);
                }
            }
        }

        // filter status
        $filterStatus = 'scheduled';
        if (!empty($request->input('filterStatus'))) {
            $filterStatus = $request->input('filterStatus');
        }
        if ($request->input('filterStatus') != 'all') {
            $calls->where('status', '=', $filterStatus);
        }

        $calls->orderBy('scheduled_date', 'asc');
        $calls->orderBy('window_start', 'asc');
        //$calls = $calls->paginate( 10 );
        $calls = $calls->get();


        // get all nurses
        $nurses = User::with('meta')
            ->with('roles')
            ->whereHas('roles', function ($q) {
                $q->where(function ($query) {
                    $query->orWhere('name', 'care-center');
                    $query->orWhere('name', 'no-ccm-care-center');
                });
            })
            ->orderBy('last_name', 'ASC')
            ->pluck('display_name', 'id');

        // filter user
        $patientList = User::with('roles')
            ->whereHas('roles', function ($q) {
                $q->where(function ($query) {
                    $query->where('name', 'participant');
                });
            })->whereDoesntHave('inboundCalls', function ($q) {
                $q->where(function ($query) {
                    $query->where('status', '=', 'scheduled');
                });
            })->where('program_id', '!=', '')
            ->orderBy('id', 'desc')
            ->get()
            ->pluck('fullNameWithId', 'id')
            ->all();

//        dd($patientList);

        return view('admin.patientCallManagement.index', compact([
            'calls',
            'date',
            'nurses',
            'filterNurse',
            'filterStatus',
            'patientList',
        ]));
    }

    /**
     * Show the form for editing an existing resource.
     *
     * @return Response
     */
    public function edit(
        Request $request,
        $id
    ) {
        if (!Auth::user()->can('users-edit-all')) {
            abort(403);
        }
        $messages = \Session::get('messages');

        $params = $request->all();

        $call = Call::find($id);
        if (!$call) {
            return response("Call not found", 401);
        }

        // get all nurses
        $nurses = User::with('meta')
            ->with('roles')
            ->whereHas('roles', function ($q) {
                $q->where(function ($query) {
                    $query->orWhere('name', 'care-center');
                    $query->orWhere('name', 'no-ccm-care-center');
                });
            })
            ->orderBy('last_name', 'ASC')
            ->pluck('display_name', 'id');

        return view('admin.patientCallManagement.edit', compact([
            'call',
            'nurses',
        ]));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function update(
        Request $request,
        $id
    ) {
        if (!Auth::user()->can('users-edit-all')) {
            abort(403);
        }
        // instantiate user
        $call = Call::find($id);
        if (!$call) {
            return response("Call not found", 401);
        }

        // input
        if ($request->input('outbound_cpm_id') && !empty($request->input('outbound_cpm_id')) && $request->input('outbound_cpm_id') != 'unassigned') {
            $call->outbound_cpm_id = $request->input('outbound_cpm_id');
        } else {
            $call->outbound_cpm_id = null;
        }
        $call->window_start = $request->input('window_start');
        $call->window_end = $request->input('window_end');
        $call->save();

        return redirect()->back()->with('messages', ['successfully updated call']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }
    
    /**
        * New Calls page with lazy-loading.
        *
        * @return Response
        */
    public function remix(Request $request)
    {
        return view('admin.patientCallManagement.remix');
    }
}
