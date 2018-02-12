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

class PracticeReportsService
{
    /**
     * @param array $practices
     * @param Carbon $date
     *
     * @return array
     */
    public function getPdfInvoiceAndPatientReport(array $practices, Carbon $date)
    {
        $invoices = [];

        foreach ($practices as $practiceId) {
            $practice = Practice::find($practiceId);

            $data = (new PracticeInvoiceGenerator($practice, $date))->generatePdf();

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
                    $data[] = $row->toArray();
                }
            } else {
                $providers = $practice->providers();

                foreach ($providers as $provider) {

                    $chargeableServices = $this->getChargeableServices($provider);

                    foreach ($chargeableServices as $service) {
                        $row    = $this->makeRow($practice, $date, $service, $provider);
                        $data[] = $row->toArray();

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
        return Excel::create("Billable Patients Report - $date", function ($excel) use ($rows) {
            $excel->sheet('Billable Patients', function ($sheet) use ($rows) {
                $sheet->fromArray($rows);
            });
        })
                    ->store($format, false, true);
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
        if ( ! $chargeableServices) {
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

        $reportName = str_random() . '-' . $date->toAtomString();

        $pathToPatientReport = $generator->makePatientReportPdf($reportName);

        $link = shortenUrl(linkToDownloadFile($pathToPatientReport, true));

        $data = $generator->getInvoiceData();

        $providerName = '';

        if ($provider) {
            $providerName = '-' . $provider->display_name;
        }

        //if a practice has a clh_pppm charge that otherwise default to the amount of the chargeable service
        if ($data['practice']->clh_pppm) {
            $lineUnitPrice = $data['practice']->clh_pppm;
        } else {
            $lineUnitPrice = $chargeableService->amount;
        }

        $rowData = [
            'RefNumber'             => (string)$data['invoice_num'],
            'Customer'              => (string)$data['bill_to'],
            'TxnDate'               => (string)$data['invoice_date'],
            'AllowOnlineACHPayment' => 'Y',
            'SalesTerm'             => (string)'Net' . ' ' . $data['practice']->term_days,
            'ToBePrinted'           => 'N',
            'ToBeEmailed'           => 'Y',
            'PT.Billing Report:'    => (string)$link,
            'Line Item'             => (string)$chargeableService->code . $providerName,
            'LineQty'               => (string)$data['billable'],
            'LineDesc'              => (string)$chargeableService->description,
            'LineUnitPrice'         => (string)'$' . ' ' . $lineUnitPrice,
            'Msg'                   => 'Thank you for your business. 

Check Payments:
CircleLink Health
Shippan Landing Workpoint
290 Harbor Drive, Stamford, CT 06902

ACH Payments:
JPMorgan Chase Bank
Routing Number (ABA): 02110361
Account Number: 693139136
Account Name: CircleLink Health
Account Address: Shippan Landing Workpoint, 290 Harbor Drive, Stamford, CT 06902

Wire Payments:
JPMorgan Chase Bank
Routing Number (ABA): 021000021
Account Number: 693139136
Account Name: Circle Link Health
Account Address: Shippan Landing Workpoint, 290 Harbor Drive, Stamford, CT 06902',
        ];

        $quickBooksRow = new QuickBooksRow($rowData);

        return $quickBooksRow;


    }

}
