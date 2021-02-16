<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Invoices;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\LocationProcessorRepository;
use CircleLinkHealth\CcmBilling\ValueObjects\PracticeQuickbooksReportData;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Collection;

class GeneratePracticeQuickbooksReportRows
{
    private Carbon $date;
    private string $patientReportUrl;
    private Practice $practice;

    /**
     * @return Collection|PracticeQuickbooksReportData[]
     */
    public function execute(): Collection
    {
        $result = collect();

        $billSettings = $this->practice->cpmSettings()->bill_to;
        if ('practice' === $billSettings) {
            $chargeableServices = $this->getChargeableServices($this->practice);
            foreach ($chargeableServices as $service) {
                $row = $this->getReportRow($this->practice, $service, $this->patientReportUrl);
                if (null == ! $row) {
                    $result->push($row);
                }
            }
        } else {
            $this->practice->providers()
                ->each(function (User $provider) use ($result) {
                    $chargeableServices = $this->getChargeableServices($provider);
                    foreach ($chargeableServices as $service) {
                        $row = $this->getReportRow($this->practice, $service, $this->patientReportUrl, $provider);
                        if (null == ! $row) {
                            $result->push($row);
                        }
                    }
                });
        }

        return $result;
    }

    public function setDate(Carbon $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function setPatientReportUrl(string $patientReportUrl): self
    {
        $this->patientReportUrl = $patientReportUrl;

        return $this;
    }

    public function setPractice(Practice $practice): self
    {
        $this->practice = $practice;

        return $this;
    }

    private function getBillableCount(Practice $practice, ChargeableService $chargeableService): int
    {
        //todo: question - does this logic cover AWV?
        return app(LocationProcessorRepository::class)
            ->approvedBillingStatuses($practice->locations->pluck('id')->toArray(), $this->date, false)
            ->whereHas('chargeableMonthlySummaries', function ($q) use ($chargeableService) {
                $q->createdOnIfNotNull($this->date, 'chargeable_month')
                    ->where('chargeable_service_id', '=', $chargeableService->id)
                    ->where('is_fulfilled', '=', true);
            })
            ->count();
    }

    private function getChargeableServices($chargeable): Collection
    {
        $chargeable->loadMissing('chargeableServices');
        $chargeableServices = $chargeable->chargeableServices;

        //defaults to CPT 99490 if practice doesnt have a chargeableService, until further notice
        //is this <i>further notice</i> ever coming ??? :)
        if ($chargeableServices->isEmpty()) {
            $chargeableServices = ChargeableService::cached()->where('code', '=', ChargeableService::CCM);
        }

        return $chargeableServices;
    }

    private function getLineUnitPrice(Practice $practice, ChargeableService $chargeableService): string
    {
        $lineUnitPrice = '';

        $chargeableServiceWithPivot = $practice->chargeableServices
            ->whereId($chargeableService->id)
            ->first();
        if ($chargeableServiceWithPivot) {
            $lineUnitPrice = $chargeableServiceWithPivot->pivot->amount;
        }

        if ( ! $lineUnitPrice) {
            if ($practice->clh_pppm) {
                $lineUnitPrice = $practice->clh_pppm;
            } else {
                $lineUnitPrice = $chargeableService->amount;
            }
        }

        return '$'.' '.$lineUnitPrice;
    }

    private function getReportRow(
        Practice $practice,
        ChargeableService $chargeableService,
        string $link,
        User $provider = null
    ): ?PracticeQuickbooksReportData {
        $billableCount = $this->getBillableCount($practice, $chargeableService);
        if ( ! $billableCount) {
            return null;
        }

        $providerName = $provider ? '-'.$provider->display_name : '';

        $result                           = new PracticeQuickbooksReportData();
        $result->invoiceNo                = incrementInvoiceNo();
        $result->customer                 = $practice->display_name ?? 'N/A';
        $result->txnDate                  = Carbon::createFromFormat('F, Y', $this->date)->endOfMonth()->toDateString();
        $result->salesTerm                = 'Net'.' '.$practice->term_days;
        $result->patientBillingReportLink = $link;
        $result->lineItem                 = $chargeableService->code.$providerName;
        $result->lineQty                  = $billableCount;
        $result->lineDesc                 = 'Software-Only' == $chargeableService->code ? 'Software-Only Platform Fee' : ($chargeableService->description ?? 'N/A');
        $result->lineUnitPrice            = $this->getLineUnitPrice($practice, $chargeableService);

        return $result;
    }
}
