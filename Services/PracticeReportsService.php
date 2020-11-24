<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Services;

use CircleLinkHealth\CpmAdmin\DTO\QuickBooksRow;
use Carbon\Carbon;
use CircleLinkHealth\Core\Exports\FromArray;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\Exceptions\InvalidConversion;
use Spatie\MediaLibrary\Models\Media;

class PracticeReportsService
{
    /**
     * @throws FileCannotBeAdded
     * @throws InvalidConversion
     *
     * @return array
     */
    public function getPdfInvoiceAndPatientReport(array $practices, Carbon $date)
    {
        $invoices = [];
        
        foreach ($practices as $practiceId) {
            $practice = Practice::find($practiceId);
            
            try {
                $data = (new PracticeInvoiceGenerator($practice, $date))->generatePdf();
            } catch (FileCannotBeAdded $e) {
                throw $e;
            } catch (InvalidConversion $e) {
                throw $e;
            }
            
            $invoices[$practice->display_name] = $data;
        }
        
        return $invoices;
    }
    
    /**
     * @param $practices
     * @param $format
     *
     * @return mixed
     */
    public function getQuickbooksReport($practices, $format, Carbon $date)
    {
        $data = [];
        
        $saasAccount = null;
        
        foreach (Practice::with(['settings', 'chargeableServices', 'saasAccount'])->whereIn('id', $practices)->get() as $practice) {
            if ( ! $saasAccount) {
                $saasAccount = $practice->saasAccount;
            }
            
            $patientReport = $this->generatePatientReportCsv($practice, $date);
            $link          = shortenUrl($patientReport->getUrl());
            
            if ('practice' == $practice->cpmSettings()->bill_to || empty($practice->cpmSettings()->bill_to)) {
                $chargeableServices = $this->getChargeableServices($practice);
                
                foreach ($chargeableServices as $service) {
                    $row = $this->makeRow($practice, $date, $service, $link);
                    
                    if (null == ! $row) {
                        $data[] = $row->toArray();
                    }
                }
            } else {
                $providers = $practice->providers();
                
                foreach ($providers as $provider) {
                    $chargeableServices = $this->getChargeableServices($provider);
                    
                    foreach ($chargeableServices as $service) {
                        $row = $this->makeRow($practice, $date, $service, $link, $provider);
                        if (null == ! $row) {
                            $data[] = $row->toArray();
                        }
                    }
                }
            }
        }
        
        if ( ! $data) {
            return false;
        }
        
        return $this->makeQuickbookReport($data, $format, $date, $saasAccount);
    }
    
    private function generatePatientReportCsv(Practice $practice, Carbon $date): Media
    {
        $generator = new PracticeInvoiceGenerator($practice, $date);
        
        $reportName = $practice->name.'-'.$date->format('Y-m').'-patients';
        
        return $generator->makePatientReportCsv($reportName);
    }
    
    /**
     * @param mixed $chargeable
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getChargeableServices($chargeable)
    {
        $chargeable->loadMissing('chargeableServices');
        
        $chargeableServices = $chargeable->chargeableServices;
        
        //defaults to CPT 99490 if practice doesnt have a chargeableService, until further notice
        if ($chargeableServices->isEmpty()) {
            $chargeableServices = ChargeableService::where('id', 1)->get();
        }
        
        return $chargeableServices;
    }
    
    /**
     * @param $rows
     * @param $format
     * @param mixed $saasAccount
     *
     * @return mixed
     */
    private function makeQuickbookReport($rows, $format, Carbon $date, $saasAccount)
    {
        return (new FromArray("Billable Patients Report - ${date}.$format", $rows))->storeAndAttachMediaTo(
            $saasAccount,
            "quickbooks_report_for_{$date->toDateString()}"
        );
    }
    
    /**
     * @param mixed|null $requestedByUserId
     *
     * @throws \Exception
     * @throws \Waavi\UrlShortener\InvalidResponseException
     *
     * @return QuickBooksRow
     */
    private function makeRow(
        Practice $practice,
        Carbon $date,
        ChargeableService $chargeableService,
        string $link,
        User $provider = null
    ) {
        $data = $practice->getInvoiceData($date->copy()->firstOfMonth(), $chargeableService->id);
        
        if (0 == $data['billable']) {
            return null;
        }
        
        $txnDate = Carbon::createFromFormat('F, Y', $data['month'])->endOfMonth()->toDateString();
        
        $providerName = '';
        
        if ($provider) {
            $providerName = '-'.$provider->display_name;
        }
        
        $lineUnitPrice = '';
        
        $chargeableServiceWithPivot = $practice->chargeableServices()->whereId($chargeableService->id)->first();
        if ($chargeableServiceWithPivot) {
            $lineUnitPrice = $chargeableServiceWithPivot->pivot->amount;
        }
        
        if ( ! $lineUnitPrice) {
            if ($data['practice']->clh_pppm) {
                $lineUnitPrice = $data['practice']->clh_pppm;
            } else {
                $lineUnitPrice = $chargeableService->amount;
            }
        }
        
        $rowData = [
            'RefNumber'             => (string) $data['invoice_num'],
            'Customer'              => (string) $data['practice']->display_name,
            'TxnDate'               => (string) $txnDate,
            'AllowOnlineACHPayment' => 'Y',
            'SalesTerm'             => (string) 'Net'.' '.$data['practice']->term_days,
            'ToBePrinted'           => 'N',
            'ToBeEmailed'           => 'Y',
            'Pt. billing report:'   => (string) $link,
            'Line Item'             => (string) $chargeableService->code.$providerName,
            'LineQty'               => (string) $data['billable'],
            'LineDesc'              => 'Software-Only' == (string) $chargeableService->code
                ? 'Software-Only Platform Fee'
                : $chargeableService->description,
            'LineUnitPrice' => (string) '$'.' '.$lineUnitPrice,
            'Msg'           => 'ACH Payments: Silicon Valley Bank
Routing Number (ABA): 121140399
Account Number: 3302397258
Account Name: CIRCLELINK HEALTH INC.
',
        ];
        
        return new QuickBooksRow($rowData);
    }
}
