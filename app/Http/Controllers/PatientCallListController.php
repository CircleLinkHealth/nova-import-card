<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\CallView;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PatientCallListController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $calls = CallView::where('nurse_id', '=', \Auth::user()->id);

        $dateFilter = 'All';
        $date       = Carbon::now();

        // filter status
        $filterStatus = 'scheduled';
        if ( ! empty($request->input('filterStatus'))) {
            $filterStatus = $request->input('filterStatus');
        }

        if ($request->has('date') && 'all' != strtolower($request->input('date'))) {
            try {
                $date = $dateFilter = Carbon::parse($request->input('date'));
            } catch (\Exception $e) {
                return redirect()->back()->withErrors('Invalid date format. Please use yyyy-mm-dd instead.');
            }
            $calls->where('scheduled_date', '=', $date->toDateString());
        }

        if ('all' != $filterStatus) {
            $calls->where('status', '=', $filterStatus);
        }

        $calls->orderBy('scheduled_date', 'asc');
        $calls->orderBy('call_time_start', 'asc');

        $calls = $calls->get()->sortByDesc(
            function ($call) {
                return 'Call Back' == $call->type;
            }
        );

        return view('patientCallList.index', compact([
            'calls',
            'dateFilter',
            'filterStatus',
        ]));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
    }
}
