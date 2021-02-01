<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\Customer\AppConfig\PatientSupportUser;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use function JmesPath\search;

class ChangePatientChargeableServiceAndTransferTimeForLegacyABP implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    protected ChargeableService $fromCs;
    protected Carbon $month;

    protected array $patientIds;
    protected ChargeableService $toCs;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $patientIds, Carbon $month, ChargeableService $fromCs, ChargeableService $toCs)
    {
        $this->patientIds = $patientIds;
        $this->month      = $month;
        $this->fromCs     = $fromCs;
        $this->toCs       = $toCs;
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
                'patientSummaries' => fn ($pms) => $pms->with('chargeableServices')->where('month_year', $this->month),
            ])
            ->whereIn('id', $this->patientIds)
            ->each(function (User $patient) use ($patientSupportUserId) {
                if ( ! $patient->primaryPractice->chargeableServices->contains($this->toCs->id)) {
                    Log::info("Patient ($patient->id) Primary Practice ({$patient->primaryPractice->id}), does not have CS ({$this->toCs->id}). Cannot attach to patient.");
                    $this->unsetPatientId($patient->id);
                    return;
                }

                $summary = $patient->patientSummaries->first();

                if ($summary->chargeableServices->contains($this->fromCs->id)) {
                    $summary->chargeableServices()->detach($this->fromCs->id);
                }

                $summary->chargeableServices()->syncWithoutDetaching([
                    $this->toCs->id,
                    [
                        'is_fulfilled' => true,
                    ],
                ]);

                $summary->actor_id = $patientSupportUserId;
                $summary->save();
            });

        if (empty($this->patientIds)){
            Log::info("Aborting Time Processing, no valid patient IDs left");
            return;
        }

        TransferTimeFromCsForLegacyABP::dispatch(
            $this->patientIds,
            $this->month,
            $this->fromCs->id,
            $this->toCs->code
        )->onQueue(getCpmQueueName(CpmConstants::LOW_QUEUE));
    }

    private function unsetPatientId(int $patientId): void
    {
        $index = array_search($patientId, $this->patientIds);

        if ($index === false){
            Log::info("Patient ID: '$patientId' not found in patient IDs array.");
            return;
        }

        unset($this->patientIds[$index]);
    }
}
