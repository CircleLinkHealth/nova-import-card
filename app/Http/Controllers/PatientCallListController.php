<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\CallView;
use App\Services\NoteService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
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
     * @param $dropdownStatus
     * @param $filterPriority
     * @param Builder $calls
     * @param string  $today
     *
     * @return Builder[]|Collection
     */
    public function filterCalls($dropdownStatus, $filterPriority, Builder $calls, string $today)
    {
        if ('completed' === $dropdownStatus && 'all' === $filterPriority) {
            $calls->whereIn('status', ['reached', 'done']);
        }

        if ('scheduled' === $dropdownStatus && 'all' === $filterPriority) {
            $calls->where('status', '=', 'scheduled');
        }

        if ('all' !== $filterPriority) {
            $calls->where('status', '=', 'scheduled')
                ->orWhere(function ($call) {
                    $call->where('asap', '=', true)
                        ->where('status', '=', 'scheduled');
                })->where('scheduled_date', '=', $today);
        }

        $calls->orderByRaw('FIELD(type, "Call Back") desc, scheduled_date desc, call_time_start asc, call_time_end asc');

        return $calls->get();
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
        $calls          = CallView::where('nurse_id', '=', $nurseId);
        $filterPriority = 'all';
        $dropdownStatus = 'scheduled';

        if ( ! empty($request->input('filterPriority'))) {
            $filterPriority = $request->input('filterPriority');
        }

        if ( ! empty($request->input('filterStatus')) && 'all' === $filterPriority) {
            $dropdownStatus = $request->input('filterStatus');
        }

        $dropdownStatusClass = 'all' !== $filterPriority
            ? ['disabled' => 'disable', 'class' => 'form-control select-picker', 'style' => 'width:32%; margin-left:-55%;']
            : ['class' => 'form-control select-picker', 'style' => 'width:32%; margin-left:-55%;'];

        $calls = $this->filterCalls($dropdownStatus, $filterPriority, $calls, $today);

        return view('patientCallList.index', compact([
            'draftNotes',
            'calls',
            'dateFilter',
            'dropdownStatus',
            'filterPriority',
            'dropdownStatusClass',
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
