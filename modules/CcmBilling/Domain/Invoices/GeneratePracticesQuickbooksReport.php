<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Invoices;

use AshAllenDesign\ShortURL\Exceptions\ShortURLException;
use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Repositories\BatchableStoreRepository;
use CircleLinkHealth\CcmBilling\ValueObjects\PracticeQuickbooksReportData;
use CircleLinkHealth\Core\Exports\FromArray;
use CircleLinkHealth\CpmAdmin\Notifications\InvoicesCreatedNotification;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class GeneratePracticesQuickbooksReport
{
    private string $batchId;
    private Carbon $date;
    private string $format;
    private array $practices;
    private int $requestedUserId;

    public function execute(): void
    {
        /** @var User $user */
        $user = User::find($this->requestedUserId);

        /** @var SaasAccount $saasAccount */
        $saasAccount = null;
        $result      = [];
        $batchRepo   = app(BatchableStoreRepository::class);

        /** @var Collection|Practice[] $practices */
        $practices = Practice::with([
            'settings',
            'chargeableServices',
            'saasAccount',
            'locations' => fn ($q) => $q->select(['id', 'practice_id']),
        ])
            ->whereIn('id', $this->practices)
            ->get();

        foreach ($practices as $practice) {
            if ( ! $saasAccount) {
                $saasAccount = $practice->saasAccount;
            }

            $patientReportUrl = $this->getPatientReportUrl($batchRepo, $practice->id);
            if ( ! $patientReportUrl) {
                Log::error("could not find patient report for $practice->id");
                continue;
            }

            (new GeneratePracticeQuickbooksReportRows())
                ->setDate($this->date)
                ->setPractice($practice)
                ->setPatientReportUrl($patientReportUrl)
                ->execute()
                ->each(function (PracticeQuickbooksReportData $row) use (&$result) {
                    $result[] = $row->toCsvRow();
                });
        }

        if (empty($result)) {
            $user->notify(new InvoicesCreatedNotification([], $this->date, $this->practices));

            return;
        }

        $quickBooksReport = $this->getReport($result, $saasAccount);
        $user->notify(new InvoicesCreatedNotification([$quickBooksReport->id], $this->date, $this->practices));
    }

    public function setBatchId(string $batchId): GeneratePracticesQuickbooksReport
    {
        $this->batchId = $batchId;

        return $this;
    }

    public function setDate(Carbon $date): GeneratePracticesQuickbooksReport
    {
        $this->date = $date;

        return $this;
    }

    public function setFormat(string $format): GeneratePracticesQuickbooksReport
    {
        $this->format = $format;

        return $this;
    }

    public function setPractices(array $practices): GeneratePracticesQuickbooksReport
    {
        $this->practices = $practices;

        return $this;
    }

    public function setRequestedUserId(int $requestedUserId): GeneratePracticesQuickbooksReport
    {
        $this->requestedUserId = $requestedUserId;

        return $this;
    }

    private function getPatientReportUrl(BatchableStoreRepository $batchRepo, int $practiceId): ?string
    {
        $patientReport = $batchRepo
            ->get($this->batchId, BatchableStoreRepository::MEDIA_TYPE, [$practiceId])
            ->first();
        if ( ! $patientReport) {
            return null;
        }

        $media            = Media::find($patientReport['data']);
        $patientReportUrl = $media->getUrl();
        try {
            $patientReportUrl = shortenUrl($patientReportUrl);
        } catch (ShortURLException $e) {
            Log::warning('could not shorten url:'.$e->getMessage());
        }

        return $patientReportUrl;
    }

    private function getReport(array $data, SaasAccount $saasAccount): Media
    {
        return (new FromArray("Billable Patients Report - {$this->date}.{$this->format}", $data))
            ->storeAndAttachMediaTo(
                $saasAccount,
                "quickbooks_report_for_{$this->date->toDateString()}"
            );
    }
}
