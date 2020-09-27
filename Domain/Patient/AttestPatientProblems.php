<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use App\Call;
use App\Models\Addendum;
use App\Note;
use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\AttestedProblem;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\SharedModels\Entities\Problem;
use Illuminate\Database\Eloquent\Collection;

class AttestPatientProblems
{
    protected ?Addendum $addendum;
    protected ?int $addendumId;

    //add in Job too
    protected ?int $attestorId;

    protected ?Call $call;

    protected ?int $callId;

    protected array $ccdProblemIds;

    protected Collection $ccdProblems;

    protected ?Carbon $chargeableMonth;

    protected ?int $patientUserId;

    //todo: deprecate
    protected ?PatientMonthlySummary $pms;

    protected ?int $pmsId;

    public function createRecords(): void
    {
        if (empty($this->ccdProblemIds)) {
            sendSlackMessage('#billing_alerts', 'Attestation failed. Insert data here.');

            return;
        }

        foreach ($this->getProblems() as $problem) {
            $this->createRecordForProblem($problem);
        }
    }

    public function forAddendum(?int $addendumId): self
    {
        $this->addendumId = $addendumId;

        return $this;
    }

    public function forCall(?int $callId): self
    {
        $this->callId = $callId;

        return $this;
    }

    public function forMonth(?Carbon $month): self
    {
        $this->chargeableMonth = $month;

        return $this;
    }

    //todo:deprecate
    public function forPms(?int $pmsId): self
    {
        $this->pmsId = $pmsId;

        return $this;
    }

    public function fromAttestor(?int $attestorId): self
    {
        $this->attestorId = $attestorId;

        return $this;
    }

    public function problemsToAttest(array $ccdProblemIds): self
    {
        $this->ccdProblemIds = $ccdProblemIds;

        return $this;
    }

    private function createRecordForProblem(Problem $problem): void
    {
        AttestedProblem::create([
            'patient_user_id'            => $this->getPatientUserId(),
            'call_id'                    => $this->callId,
            'patient_monthly_summary_id' => $this->pmsId ?? $this->getPms()->id ?? null,
            'chargeable_month'           => $this->getChargeableMonth(),
            'addendum_id'                => $this->addendumId,
            'ccd_problem_id'             => $problem->id,
            'ccd_problem_name'           => $problem->name,
            'ccd_problem_icd_10_code'    => $problem->icd10Code(),
            'attestor_id'                => $this->getAttestorId(),
        ]);
    }

    private function getAddendum(): ?Addendum
    {
        if (is_null($this->addendumId)) {
            return null;
        }

        if ( ! isset($this->addendum)) {
            $this->addendum = Addendum::with(['addendumable'])
                ->where('addendumable_type', Note::class)
                ->firstOrFail($this->addendumId);
        }

        return $this->addendum;
    }

    private function getAttestorId(): ?int
    {
        if ( ! isset($this->attestorId)) {
            $this->attestorId = $this->getCall()->note->author_id
                ?? $this->getAddendum()->author_user_id
                ?? auth()->id()
                ?? null;
        }

        return $this->attestorId;
    }

    private function getCall(): ?Call
    {
        if (is_null($this->callId)) {
            return null;
        }

        if ( ! isset($this->call)) {
            $this->call = Call::with('note')
                ->findOrFail($this->callId);
        }

        return $this->call;
    }

    private function getCcdProblemIds(): array
    {
        return $this->ccdProblemIds ?? [];
    }

    private function getChargeableMonth(): ?Carbon
    {
        if (isset($this->chargeableMonth)) {
            return $this->chargeableMonth;
        }

        $date = $this->getMonthFromCall()
                ?? $this->getMonthFromAddendumNote()
                ?? $this->getMonthFromPms();

        if (is_null($date)) {
            $this->sendSlackWarning();

            return null;
        }
        
        $this->chargeableMonth = $date;

        return $this->chargeableMonth;
    }

    private function getMonthFromAddendumNote(): ?Carbon
    {
        return $this->startOfMonthOrNull($this->getAddendum()->addendumable->performed_at ?? null);
    }

    private function getMonthFromCall(): ?Carbon
    {
        return $this->startOfMonthOrNull($this->getCall()->called_date ?? null);
    }

    private function getMonthFromPms(): ?Carbon
    {
        return $this->startOfMonthOrNull($this->getPms()->month_year ?? null);
    }

    private function getPatientUserId(): int
    {
        if ( ! isset($this->patientUserId)) {
            $this->patientUserId = $this->getCall()->note->patient_id
                ?? $this->getProblems()->first()->patient_id;
        }

        return $this->patientUserId;
    }

    private function getPms(): ?PatientMonthlySummary
    {
        if (is_null($this->pmsId)) {
            $this->pms = PatientMonthlySummary::getForMonth(
                $this->chargeableMonth
                ?? $this->getMonthFromCall()
                ?? $this->getMonthFromAddendumNote()
            )
                ->where('patient_id', $this->getPatientUserId())
                ->first();
        }

        if (isset($this->pmsId) && ! isset($this->pms)) {
            $this->pms = PatientMonthlySummary::firstOrFail($this->pmsId);
        }

        return $this->pms;
    }

    private function getProblems(): Collection
    {
        if ( ! isset($this->ccdProblems)) {
            $this->ccdProblems = Problem::with([
                'cpmProblem',
                'icd10Codes',
            ])
                ->find($this->getCcdProblemIds());
        }

        return $this->ccdProblems;
    }

    private function sendSlackWarning(): void
    {
        $problemsString = implode(',', $this->ccdProblemIds);
        sendSlackMessage(
            '#billing_alerts',
            "Warning:  Attestation for problems: {$problemsString} for patient ({$this->getPatientUserId()}) is incomplete. Could not determine chargeable month."
        );
    }

    private function startOfMonthOrNull($date = null): ?Carbon
    {
        return ! is_null($date) ? Carbon::parse($date)->startOfMonth() : null;
    }
}
