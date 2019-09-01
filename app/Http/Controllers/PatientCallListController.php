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
        $date    = Carbon::parse(now())->copy()->toDateString();
        $asap    = 'ASAP';

        $draftNotes = $noteService->getUserDraftNotes($nurseId);

        $calls = CallView::where('nurse_id', '=', $nurseId);

        $filterStatus = 'scheduled';
        if ( ! empty($request->input('filterStatus'))) {
            $filterStatus = $request->input('filterStatus');
        }

        $filterPriority = 'all';
        if ( ! empty($request->input('filterPriority'))) {
            $filterPriority = $request->input('filterPriority');
        }

        if ('all' != $filterStatus) {
            $calls->where('status', '=', $filterStatus);
        }

        if ('all' !== $filterPriority) {
            $calls->where('scheduled_date', '=', $date)
                ->orWhere('call_time_start', '=', $asap);
        }
        $calls->orderByRaw('FIELD(type, "Call Back") desc, scheduled_date asc, call_time_start asc, call_time_end asc');

        $calls = $calls->get();

        return view('patientCallList.index', compact([
            'draftNotes',
            'calls',
            'dateFilter',
            'filterStatus',
            'filterPriority',
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
