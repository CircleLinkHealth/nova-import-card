<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Call;
use App\Console\Commands\CountPatientMonthlySummaryCalls;
use App\Note;
use App\Services\NoteService;
use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\NurseInvoices\Console\Commands\GenerateMonthlyInvoicesForNonDemoNurses;
use Illuminate\Http\Request;

class CallsDashboardController extends Controller
{
    public function create(Request $request)
    {
        $note = Note::with(['patient', 'author'])->where('id', $request['noteId'])->first();

        if ($note) {
            $call = $note->call()->first();
            if ($call) {
                return view('admin.CallsDashboard.edit', compact(['note', 'call']));
            }
            $nurses = User::ofType('care-center')->get();

            return view('admin.CallsDashboard.create-call', compact(['note', 'nurses']));
        }
        $message = 'Note Does Not Exist.';

        return redirect()->route('CallsDashboard.index')->with('msg', $message);
    }

    public function createCall(Request $request, NoteService $service)
    {
        /** @var Note $note */
        $note = Note::with(['patient', 'author', 'call'])
            ->where('id', $request['noteId'])
            ->first();
        $call = $note->call;
        if ($call) {
            return view('admin.CallsDashboard.edit', compact(['note', 'call']));
        }

        $status = $request['status'];
        /** @var User $patient */
        $patient = User::find($note->patient_id);
        /** @var User $nurse */
        $nurse = User::with([
            'nurseInfo' => function ($q) {
                $q->select(['id', 'user_id']);
            },
        ])
            ->find($request['nurseId']);
        $phone_direction = $request['direction'];

        $service->storeCallForNote(
            $note,
            $status,
            $patient,
            $nurse,
            $phone_direction,
            null
        );

        // modify nurse_care_rate_logs
        if (Call::REACHED === $status) {
            $this->modifyNurseCareRateLogs($nurse, $note, Call::NOT_REACHED, Call::REACHED);
        }

        $this->reCalculatePms($patient->id, $note->created_at->toDateString());

        $message = 'Call Successfully Added to Note! Nurse invoice is being re-generated. Patient calls are being re-counted!';

        return redirect()->route('CallsDashboard.index')->with('msg', $message);
    }

    public function edit(Request $request)
    {
        /** @var Call $call */
        $call = Call::with([
            'note',
            'outboundUser' => function ($q) {
                $q->without(['perms', 'roles'])
                    ->with(['nurseInfo']);
            },
        ])->findOrFail($request['callId']);

        $newStatus = $request['status'];
        $oldStatus = $call->status;

        if ($oldStatus === $newStatus) {
            $message = 'Call Status Not Changed.';

            return redirect()
                ->route('CallsDashboard.create', ['noteId' => $request['noteId']])
                ->with('msg', $message);
        }

        $call->note->successful_clinical_call = Call::REACHED === $newStatus ? 1 : 0;
        $call->note->save();
        $call->status = $newStatus;
        $call->save();
        $this->modifyNurseCareRateLogs($call->outboundUser, $call->note, $oldStatus, $newStatus);

        $this->reCalculatePms($call->inbound_cpm_id, $call->note->created_at->toDateString());

        return redirect()
            ->route('CallsDashboard.create', ['noteId' => $request['noteId']])
            ->with('msg', 'Call Status successfully changed! Nurse invoice is being re-generated. Patient calls are being re-counted!');
    }

    public function index()
    {
        return view('admin.CallsDashboard.index');
    }

    private function modifyNurseCareRateLogs(User $nurse, Note $note, string $oldStatus, string $newStatus)
    {
        NurseCareRateLog::whereBetween('created_at', [$note->created_at->copy()->startOfDay(), $note->updated_at->copy()->endOfDay()])
            ->where('nurse_id', '=', $nurse->nurseInfo->id)
            ->where('patient_user_id', '=', $note->patient_id)
            ->whereHas('activity', function ($q) {
                $q->whereIn('type', ['Patient Note Creation', 'Patient Note Edit']);
            })
            ->where('is_successful_call', '=', Call::REACHED === $oldStatus ? 1 : 0)
            ->limit(1)
            ->update(['is_successful_call' => Call::REACHED === $newStatus ? 1 : 0]);

        // Re-generate nurse invoice
        \Artisan::call(GenerateMonthlyInvoicesForNonDemoNurses::class, [
            'month'   => $note->created_at->toDateString(),
            'userIds' => "$nurse->id",
        ]);
    }

    private function reCalculatePms($patientId, $forDate)
    {
        // Re-counts calls for patient
        \Artisan::call(CountPatientMonthlySummaryCalls::class, [
            'date'    => $forDate,
            'userIds' => "$patientId",
        ]);
    }
}
