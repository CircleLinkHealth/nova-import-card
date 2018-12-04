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
        $calls = Call::where('outbound_cpm_id', '=', \Auth::user()->id);

        $dateFilter = 'All';
        $date       = Carbon::now();
        if ($request->has('date') && strtolower($request->input('date')) != 'all') {
            try {
                $date = $dateFilter = Carbon::parse($request->input('date'));
            } catch (\Exception $e) {
                return redirect()->back()->withErrors("Invalid date format. Please use yyyy-mm-dd instead.");
            }

            $calls->where('scheduled_date', '=', $date->toDateString());

        }

        $calls->with([
            'inboundUser' => function ($u) use ($date) {
                $u->with([
                    'patientSummaries' => function ($q) use ($date) {
                        $q->where('month_year', $date->startOfMonth()->toDateString())
                          ->orderBy('id', 'desc');
                    },
                    'patientInfo.contactWindows',
                ]);
            },
        ]);

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
            'dateFilter',
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
