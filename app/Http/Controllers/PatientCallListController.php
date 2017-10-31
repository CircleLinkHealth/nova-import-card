<?php namespace App\Http\Controllers;

use App\Call;
use DateTime;
use Illuminate\Http\Request;

class PatientCallListController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {

        // ACTIONS
//        if ($request->input('action') && $request->input('action') == 'unassign') {
//            if ($request->input('id') && !empty($request->input('id'))) {
//                $call = Call::find($request->input('id'));
//                if ($call) {
//                    $call->outbound_cpm_id = null;
//                    $call->save();
//                }
//
//                return redirect()->back()->with('messages', ['successfully unassigned call']);
//            }
//        }

        // get all calls
        $calls = Call::where('id', '>', 0);

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

        // filter status
        $filterStatus = 'scheduled';
        if (!empty($request->input('filterStatus'))) {
            $filterStatus = $request->input('filterStatus');
        }
        if ($request->input('filterStatus') != 'all') {
            $calls->where('status', '=', $filterStatus);
        }

        // filter nurse
        $calls->where('outbound_cpm_id', '=', \Auth::user()->id);

        $calls->orderBy('scheduled_date', 'asc');
        $calls->orderBy('window_start', 'asc');
        //$calls = $calls->paginate( 10 );
        $calls = $calls->get();


        return view('patientCallList.index', compact([
            'calls',
            'date',
            'filterStatus',
        ]));
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
}
