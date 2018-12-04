<?php
/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 9/30/16
 * Time: 6:09 PM
 */

namespace App\Services;

use App\Billing\Practices\PracticeInvoiceGenerator;
use App\ChargeableService;
use App\Practice;
use App\User;
use App\ValueObjects\QuickBooksRow;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\Exceptions\InvalidConversion;

class PracticeReportsService
{
    /**
     * @param array $practices
     * @param Carbon $date
     *
     * @return array
     * @throws InvalidConversion
     * @throws FileCannotBeAdded
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
     * @param Carbon $date
     *
     * @return mixed
     */
    public function getQuickbooksReport($practices, $format, Carbon $date)
    {
        $data = [];

        foreach ($practices as $practiceId) {
            $practice = Practice::find($practiceId);

            if ($practice->cpmSettings()->bill_to == 'practice') {
                $chargeableServices = $this->getChargeableServices($practice);


                foreach ($chargeableServices as $service) {
                    $row    = $this->makeRow($practice, $date, $service);

                    if (!$row == null) {
                        $data[] = $row->toArray();
                    }
                }
            } else {
                $providers = $practice->providers();

                foreach ($providers as $provider) {
                    $chargeableServices = $this->getChargeableServices($provider);

                    foreach ($chargeableServices as $service) {
                        $row    = $this->makeRow($practice, $date, $service, $provider);
                        if (!$row == null) {
                            $data[] = $row->toArray();
                        }
                    }
                }
            }
        }

        return $this->makeQuickbookReport($data, $format, $date);
    }

    /**
     * @param $rows
     * @param $format
     * @param Carbon $date
     *
     * @return mixed
     */
    private function makeQuickbookReport($rows, $format, Carbon $date)
    {
        $report = Excel::create("Billable Patients Report - $date", function ($excel) use ($rows) {
            $excel->sheet('Billable Patients', function ($sheet) use ($rows) {
                $sheet->fromArray($rows);
            });
        })
                    ->store($format, false, true);

        return auth()->user()
            ->saasAccount
            ->addMedia($report['full'])
            ->toMediaCollection("quickbooks_report_for_{$date->toDateString()}");
    }

    /**
     *
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getChargeableServices($chargeable)
    {
        $chargeableServices = $chargeable->chargeableServices()->get();

        //defaults to CPT 99490 if practice doesnt have a chargeableService, until further notice
        if (! $chargeableServices) {
            $chargeableServices = ChargeableService::where('id', 1)->get();
        }

        return $chargeableServices;
    }

    /**
     * @param Practice $practice
     * @param Carbon $date
     * @param ChargeableService $chargeableService
     *
     * @return QuickBooksRow
     * @throws \Exception
     * @throws \Waavi\UrlShortener\InvalidResponseException
     */
    private function makeRow(
        Practice $practice,
        Carbon $date,
        ChargeableService $chargeableService,
        User $provider = null
    ) {
        $generator = new PracticeInvoiceGenerator($practice, $date);

        $reportName = $practice->name . '-' . $date->format('Y-m') . '-patients';

        $patientReport = $generator->makePatientReportPdf($reportName);

        $link = shortenUrl($patientReport->getUrl());

        $data = $generator->getInvoiceData($chargeableService->id);

        if ($data['billable'] == 0) {
            return null;
        }

        $txnDate = Carbon::createFromFormat('F, Y', $data['month'])->endOfMonth()->toDateString();


        $providerName = '';


        if ($provider) {
            $providerName = '-' . $provider->display_name;
        }

        $lineUnitPrice = '';

        $chargeableServiceWithPivot = $practice->chargeableServices()->whereId($chargeableService->id)->first();
        if ($chargeableServiceWithPivot) {
            $lineUnitPrice = $chargeableServiceWithPivot->pivot->amount;
        }

        if (!$lineUnitPrice) {
            if ($data['practice']->clh_pppm) {
                $lineUnitPrice = $data['practice']->clh_pppm;
            } else {
                $lineUnitPrice = $chargeableService->amount;
            }
        }

        $rowData = [
            'RefNumber'             => (string)$data['invoice_num'],
            'Customer'              => (string)$data['practice']->display_name,
            'TxnDate'               => (string)$txnDate,
            'AllowOnlineACHPayment' => 'Y',
            'SalesTerm'             => (string)'Net' . ' ' . $data['practice']->term_days,
            'ToBePrinted'           => 'N',
            'ToBeEmailed'           => 'Y',
            'Pt. billing report:'    => (string)$link,
            'Line Item'             => (string)$chargeableService->code . $providerName,
            'LineQty'               => (string)$data['billable'],
            'LineDesc'              => (string)$chargeableService->description,
            'LineUnitPrice'         => (string)'$' . ' ' . $lineUnitPrice,
            'Msg'                   => 'Send Check Payments to:
CircleLink Health Inc. 
C/O I2BF Ventures
304 Park Avenue South, 9th FLoor
New York, NY 10010

ACH Payments: JPMorgan Chase Bank 
Routing Number (ABA): 02110361 
Account Number: 693139136 
Account Name: CircleLink Health Account 
Address: Shippan Landing Workpoint, 290 Harbor Drive, Stamford, CT 06902 

Wire Payments: JPMorgan Chase Bank 
Routing Number (ABA): 021000021 
Account Number: 693139136 
Account Name: Circle Link Health Account 
Address: Shippan Landing Workpoint, 290 Harbor Drive, Stamford, CT 06902
',
        ];

        $quickBooksRow = new QuickBooksRow($rowData);

        return $quickBooksRow;
    }
}
