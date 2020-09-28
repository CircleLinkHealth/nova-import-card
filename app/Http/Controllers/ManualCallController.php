<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Algorithms\Calls\NextCallSuggestor\Suggestion as NextCallSuggestion;
use App\Algorithms\Calls\NextCallSuggestor\Suggestor as NextCallDateSuggestor;
use App\Algorithms\Calls\NurseFinder\NurseFinderEloquentRepository;
use App\Http\Requests\ShowCreateManualCallForm;
use App\Http\Requests\StoreManualScheduledCall;
use App\Services\Calls\SchedulerService;
use App\ValueObjects\CreateManualCallAfterNote;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManualCallController extends Controller
{
    const SESSION_KEY = 'last_call_status_used_for_';

    private SchedulerService $service;

    public function __construct(SchedulerService $service)
    {
        $this->service = $service;
    }

    public function create(ShowCreateManualCallForm $request)
    {
        $message = \Session::pull(self::SESSION_KEY);

        if ( ! $message instanceof CreateManualCallAfterNote) {
            throw new \DomainException('Type Error in ManualCallController `$message` should be an instance of.');
        }
        $nextCallSuggestion = app(NextCallDateSuggestor::class)
            ->handle($message->getPatient(), $message->getCallHandler());

        return view('wpUsers.patient.calls.create', ($nextCallSuggestion ?? new NextCallSuggestion())->toArray())
            ->with('patient', $message->getPatient())
            ->with('messages', ['Successfully Created Note!']);
    }

    /**
     * @param Request $request
     * @param $patientId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreManualScheduledCall $request, $patientId)
    {
        $input = $request->all();

        $window_start = Carbon::parse($input['window_start'])->format('H:i');
        $window_end   = Carbon::parse($input['window_end'])->format('H:i');

        //If the suggested date doesn't match the one in the input,
        //the scheduler has changed the date, mark it.
        $scheduler = ($input['suggested_date'] == $input['date'])
            ? 'core algorithm'
            : Auth::user()->id;

        $is_manual = 'core algorithm' !== $scheduler;

        //We are storing the current caller as the next scheduled call's outbound cpm_id
        $this->service->storeScheduledCall(
            $patientId,
            $window_start,
            $window_end,
            $input['date'],
            $scheduler,
            optional(app(NurseFinderEloquentRepository::class)->find($patientId))->id,
            isset($input['attempt_note'])
                ? $input['attempt_note']
                : '',
            $is_manual
        );

        return redirect()->route('patient.note.index', [
            'patientId' => $patientId,
        ])
            ->with('messages', ['Successfully Created Note']);
    }
}
