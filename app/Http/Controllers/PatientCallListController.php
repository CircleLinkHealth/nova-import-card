<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\CallView;
use App\Services\NoteService;
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
    public function index(Request $request, NoteService $noteService)
    {
        $nurseId = \Auth::user()->id;

        $draftNotes = $noteService->getUserDraftNotes($nurseId);

        $calls = CallView::where('nurse_id', '=', $nurseId);

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

        $calls->orderByRaw('FIELD(type, "Call Back") desc, scheduled_date asc, call_time_start asc, call_time_end asc');

        $calls = $calls->get();

        return view('patientCallList.index', compact([
            'draftNotes',
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
