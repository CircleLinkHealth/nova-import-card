<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Controllers;

use CircleLinkHealth\CpmAdmin\Console\Commands\CountPatientMonthlySummaryCalls;
use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Services\NoteService;
use CircleLinkHealth\NurseInvoices\Console\Commands\GenerateMonthlyInvoicesForNonDemoNurses;
use CircleLinkHealth\SharedModels\Entities\Call;
use CircleLinkHealth\SharedModels\Entities\Note;
use CircleLinkHealth\SharedModels\Notifications\PatientUnsuccessfulCallNotification;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class CallsDashboardController extends Controller
{
    public function create(Request $request)
    {
        /** @var Note $note */
        $note = Note::with([
            'patient',
            'author',
            'call' => function ($q) {
                $q->where(function ($q2) {
                    $q2->whereNull('type')
                        ->orWhere('type', '=', 'call')
                        ->orWhere('sub_type', '=', 'Call Back');
                });
            },
        ])
            ->where('id', $request['noteId'])
            ->first();

        if ($note) {
            $call = $note->call;
            if ($call) {
                return view('cpm-admin::admin.CallsDashboard.edit', compact(['note', 'call']));
            }
            $nurses = User::ofType('care-center')
                ->has('nurseInfo')
                ->get(['id', 'display_name']);

            return view('cpm-admin::admin.CallsDashboard.create-call', compact(['note', 'nurses']));
        }
        $message = 'Note Does Not Exist.';

        return redirect()->route('CallsDashboard.index')->with('msg', $message);
    }

    public function createCall(Request $request, NoteService $service)
    {
        /** @var Note $note */
        $note = Note::with([
            'patient.patientInfo',
            'author',
            'call' => function ($q) {
                $q->where(function ($q2) {
                    $q2->whereNull('type')
                        ->orWhere('type', '=', 'call')
                        ->orWhere('sub_type', '=', 'Call Back');
                });
            },
        ])
            ->where('id', $request['noteId'])
            ->first();
        $call = $note->call;
        if ($call) {
            return view('cpm-admin::admin.CallsDashboard.edit', compact(['note', 'call']));
        }

        $status  = $request['status'];
        $patient = $note->patient;

        /** @var User $nurse */
        $nurse = User::with(['nurseInfo:id,user_id'])
            ->has('nurseInfo')
            ->findOrFail($request['nurseId']);

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
            'inboundUser.patientInfo',
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
        return view('cpm-admin::admin.CallsDashboard.index');
    }

    private function modifyNurseCareRateLogs(User $nurse, Note $note, string $oldStatus, string $newStatus)
    {
        if (is_null($nurse->nurseInfo)) {
            Log::critical("User (ID:$nurse->id), author of note (ID:$note->id) does not have nurseInfo. Cannot modify nurse care rate logs or generate nurse invoices.");

            return;
        }

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
