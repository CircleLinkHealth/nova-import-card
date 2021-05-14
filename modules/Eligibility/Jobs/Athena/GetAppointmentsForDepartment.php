<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs\Athena;

use Carbon\Carbon;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use CircleLinkHealth\SharedModels\Entities\TargetPatient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GetAppointmentsForDepartment implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var null
     */
    protected $batchId;
    /**
     * @var int
     */
    protected $departmentId;
    /**
     * @var int
     */
    protected $ehrPracticeId;
    /**
     * @var Carbon
     */
    protected $end;
    /**
     * @var bool
     */
    protected $offset;
    /**
     * @var Carbon
     */
    protected $start;

    /**
     * Create a new job instance.
     *
     * @param bool $offset
     * @param null $batchId
     */
    public function __construct(
        int $departmentId,
        int $ehrPracticeId,
        Carbon $start,
        Carbon $end,
        $offset = false,
        $batchId = null
    ) {
        $this->departmentId  = $departmentId;
        $this->ehrPracticeId = $ehrPracticeId;
        $this->start         = $start;
        $this->end           = $end;
        $this->offset        = $offset;
        $this->batchId       = $batchId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $this->logStart();

        $offsetBy = 0;

        if ($this->offset) {
            $offsetBy = TargetPatient::where('ehr_practice_id', $this->ehrPracticeId)
                ->where('ehr_department_id', $this->departmentId)
                ->count();
        }

        $start = $this->start->format('m/d/Y');
        $end   = $this->end->format('m/d/Y');

        $response = app(AthenaApiImplementation::class)->getBookedAppointments(
            $this->ehrPracticeId,
            $start,
            $end,
            $this->departmentId,
            $offsetBy
        );

        if ( ! isset($response['appointments'])) {
            return;
        }

        $count = count($response['appointments']);

        $this->logEnd($count);

        if (0 == $count) {
            return;
        }

        foreach ($response['appointments'] as $bookedAppointment) {
            $ehrPatientId = $bookedAppointment['patientid'];
            $departmentId = $bookedAppointment['departmentid'];

            if ( ! $ehrPatientId) {
                continue;
            }

            $target = TargetPatient::updateOrCreate(
                [
                    'practice_id'       => Practice::where('external_id', $this->ehrPracticeId)->value('id'),
                    'ehr_id'            => CpmConstants::athenaEhrId(),
                    'ehr_patient_id'    => $ehrPatientId,
                    'ehr_practice_id'   => $this->ehrPracticeId,
                    'ehr_department_id' => $departmentId,
                ]
            );

            if (null !== $this->batchId) {
                $target->batch_id = $this->batchId;
            }

            if ( ! $target->status) {
                $target->status = 'to_process';
                $target->save();
            }
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return [
            'athena',
            'batchid:'.$this->batchId,
            'departmentid:'.$this->departmentId,
            'ehrpracticeid:'.$this->ehrPracticeId,
            'end:'.$this->end->toDateTimeString(),
            'offset:'.$this->offset,
            'start:'.$this->start->toDateTimeString(),
        ];
    }

    private function logEnd(int $count)
    {
        Log::debug(
            'Start GetAppointmentsForDepartment'.PHP_EOL
            .now()->toDateTimeString().PHP_EOL
            ."Batch[{$this->batchId}]".PHP_EOL
            .'from['.$this->start->toDateTimeString().']'.PHP_EOL
            .'to['.$this->end->toDateTimeString().']'.PHP_EOL
            ."department[{$this->departmentId}]".PHP_EOL
            ."practice[{$this->ehrPracticeId}]".PHP_EOL
            ."offset[{$this->$this->offset}]".PHP_EOL
            ."pulled[$count] appointments".PHP_EOL
        );
    }

    private function logStart()
    {
        Log::debug(
            'Start GetAppointmentsForDepartment'.PHP_EOL
            .now()->toDateTimeString().PHP_EOL
            ."Batch[{$this->batchId}]".PHP_EOL
            .'from['.$this->start->toDateTimeString().']'.PHP_EOL
            .'to['.$this->end->toDateTimeString().']'.PHP_EOL
            ."department[{$this->departmentId}]".PHP_EOL
            ."practice[{$this->ehrPracticeId}]".PHP_EOL
            ."offset[{$this->$this->offset}]".PHP_EOL
        );
    }
}
