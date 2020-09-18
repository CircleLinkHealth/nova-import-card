<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Controllers;

use CircleLinkHealth\CpmAdmin\Notifications\PatientUnsuccessfulCallNotification;
use CircleLinkHealth\CpmAdmin\Console\Commands\CountPatientMonthlySummaryCalls;
use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Services\NoteService;
use CircleLinkHealth\NurseInvoices\Console\Commands\GenerateMonthlyInvoicesForNonDemoNurses;
use CircleLinkHealth\SharedModels\Entities\Call;
use CircleLinkHealth\SharedModels\Entities\Note;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

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
        $note = Note::with([
            'patient' => function ($q) {
                $q->without(['roles', 'perms'])
                    ->with(['patientInfo']);
            },
            'author',
            'call',
        ])
            ->where('id', $request['noteId'])
            ->first();
        $call = $note->call;
        if ($call) {
            return view('admin.CallsDashboard.edit', compact(['note', 'call']));
        }

        $status  = $request['status'];
        $patient = $note->patient;
        /** @var User $nurse */
        $nurse = User::with([
            'nurseInfo' => function ($q) {
                $q->select(['id', 'user_id']);
            },
        ])
            ->find($request['nurseId']);
        $phone_direction = $request['direction'];

        $call = $service->storeCallForNote(
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
        $this->updatePatientInfo($patient->patientInfo, Call::REACHED === $status);

        $message = 'Call Successfully Added to Note! Nurse invoice is being re-generated. Patient calls are being re-counted!';

        if ('yes' === $request->input('notify-patient', 'no')) {
            $patient->notify(new PatientUnsuccessfulCallNotification($call, false));
            $message .= ' We will also send sms/email notifications to patient!';
        }

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

        //nurse invoice
        $this->modifyNurseCareRateLogs($call->outboundUser, $call->note, $oldStatus, $newStatus);

        //patient info
        $this->reCalculatePms($call->inbound_cpm_id, $call->note->created_at->toDateString());
        $this->updatePatientInfo($call->inboundUser->patientInfo, Call::REACHED === $newStatus);

        $message = 'Call Status successfully changed! Nurse invoice is being re-generated. Patient calls are being re-counted!';

        if ('yes' === $request->input('notify-patient', 'no')) {
            $call->inboundUser->notify(new PatientUnsuccessfulCallNotification($call, false));
            $message .= ' We will also send sms/email notifications to patient!';
        }

        return redirect()
            ->route('CallsDashboard.create', ['noteId' => $request['noteId']])
            ->with('msg', $message);
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

    private function updatePatientInfo(Patient $patient, bool $successfulCall)
    {
        if ($successfulCall) {
            $patient->no_call_attempts_since_last_success = 0;
            $patient->save();
        }
    }
}
