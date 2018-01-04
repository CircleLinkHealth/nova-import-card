<?php namespace App\Http\Controllers;

use App\Call;
use Carbon\Carbon;
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
        $calls = Call::where('outbound_cpm_id', '=', \Auth::user()->id)
                     ->with([
                         'inboundUser.patientSummaries' => function ($q) {
                             $q->orderBy('id', 'desc');
                         },
                     ]);

        $date = 'All';
        if ($request->input('date')) {
            if (strtolower($request->input('date')) != 'all') {
                $date = Carbon::parse($request->input('date'))->toDateString();
                $calls->where('scheduled_date', '=', $date);
            }
        }

        // filter status
        $filterStatus = 'scheduled';
        if ( ! empty($request->input('filterStatus'))) {
            $filterStatus = $request->input('filterStatus');
        }

        if ($filterStatus != 'all') {
            $calls->where('status', '=', $filterStatus);
        }

        $calls->orderBy('scheduled_date', 'asc');
        $calls->orderBy('window_start', 'asc');

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
