<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use CircleLinkHealth\SharedModels\Entities\Call;
use App\Models\Addendum;
use App\Services\CallService;
use App\Services\NoteService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PatientCallListController extends Controller
{
    /**
     * @var CallService
     */
    protected $service;

    public function __construct(CallService $service)
    {
        $this->service = $service;
    }

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
    {//note:here nurseId is actually userId.
        $nurseId        = \Auth::user()->id;
        $today          = Carbon::parse(now())->copy()->toDateString();
        $draftNotes     = $noteService->getUserDraftNotes($nurseId);
        $filterPriority = 'all';
        $dropdownStatus = 'scheduled';

        if ( ! empty($request->input('filterPriority'))) {
            $filterPriority = $request->input('filterPriority');
        }

        if ( ! empty($request->input('filterStatus')) && 'all' === $filterPriority) {
            $dropdownStatus = $request->input('filterStatus');
        }

        $dropdownStatusClass = 'all' !== $filterPriority
            ? ['disabled' => 'disable',
                'class'   => 'form-control select-picker',
                'style'   => 'width:32%; margin-left:-55%;', ]
            : ['class' => 'form-control select-picker', 'style' => 'width:32%; margin-left:-55%;'];

        $calls = $this->service->filterCalls($dropdownStatus, $filterPriority, $today, $nurseId);

        return view('patientCallList.index', compact([
            'draftNotes',
            'calls',
            'dropdownStatus',
            'filterPriority',
            'dropdownStatusClass',
        ]));
    }

    /**
     * @param $callId
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function markAddendumActivitiesDone($callId)
    {
        $call   = Call::findOrFail($callId);
        $noteId = $call->note_id;

        $addendum = Addendum::where('addendumable_id', $noteId)->first();

        $addendum->markActivitiesAsDone();
        $addendum->markAllAttachmentNotificationsAsRead();

        return redirect(route('patient.note.view', ['patientId' => $call->inbound_cpm_id, 'noteId' => $noteId]));
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
