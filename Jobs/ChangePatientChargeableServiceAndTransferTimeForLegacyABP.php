<?php

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\Customer\AppConfig\PatientSupportUser;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class ChangePatientChargeableServiceAndTransferTimeForLegacyABP implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $patientIds;
    protected Carbon $month;
    protected int $fromCsId;
    protected int $toCsId;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $patientIds, Carbon $month, int $fromCsId, int $toCsId)
    {
        $this->patientIds = $patientIds;
        $this->month = $month;
        $this->fromCsId = $fromCsId;
        $this->toCsId = $toCsId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $patientSupportUserId = PatientSupportUser::id();
        User::ofType('participant')
            ->whereHas('patientInfo')
            ->with([
                'primaryPractice.chargeableServices',
                'patientSummaries' => fn($pms) => $pms->with('chargeableServices')->where('month_year', $this->month),
            ])
            ->whereIn('id', $this->patientIds)
            ->each(function (User $patient) use ($patientSupportUserId) {
                if ($patient->primaryPractice->chargeableServices->contains($this->toCsId)) {
                    Log::info("Patient ($patient->id) Primary Practice ({$patient->primaryPractice->id}), does not have CS ({$this->toCsId}). Cannot attach to patient.");

                    return;
                }

                $summary = $patient->patientSummaries->first();

                if ($summary->chargeableServices->contains($this->fromCsId)) {
                    $summary->chargeableServices->detach($this->fromCsId);
                }

                $summary->chargeableServices->syncWithoutDetaching([
                    $this->toCsId,
                    [
                        'is_fulfilled' => true,
                    ],
                ]);

                $summary->actorId = $patientSupportUserId;
                $summary->save();
            });


        TransferTimeFromCsForLegacyABP::dispatch(
                $this->patientIds,
                $this->month,
                $this->fromCsId,
                $this->toCsId
        )->onQueue(getCpmQueueName(CpmConstants::LOW_QUEUE));

    }
}
