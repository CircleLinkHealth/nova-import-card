<?php
/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 9/30/16
 * Time: 6:09 PM
 */

namespace App\Services;

use App\Billing\Practices\PracticeInvoiceGenerator;
use App\Practice;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class PracticeReportsService
{
    /**
     * @param array $practices
     * @param Carbon $date
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

    public function getQuickbooksReport($practices, $format, Carbon $date)
    {
        $data = [];

        foreach ($practices as $practiceId) {
            $practice = Practice::find($practiceId);

            $data[] = $this->makeRow($practice, $date);
        }

        return $this->makeQuickbookReport($data, $format, $date);

    }

    private function makeQuickbookReport($rows, $format, Carbon $date)
    {

        return Excel::create("Billable Patients Report - $date", function ($excel) use ($rows) {
            $excel->sheet('Billable Patients', function ($sheet) use ($rows) {
                $sheet->fromArray($rows);
            });
        })
            ->store($format, false, true);
    }

    private function makeRow(Practice $practice, Carbon $date)
    {
        $generator = new PracticeInvoiceGenerator($practice, $date);

        $reportName = str_random() . '-' . $date->toDateTimeString();

        $pathToPatientReport = $generator->makePatientReportPdf($reportName);

        $link = shortenUrl(linkToDownloadFile($pathToPatientReport, true));

        $data = $generator->getInvoiceData();

        return [
            'RefNumber'             => (string)$data['invoice_num'],
            'Customer'              => (string)$data['bill_to'],
            'TxnDate'               => (string)$data['invoice_date'],
            'AllowOnlineACHPayment' => 'Y',
            'SalesTerm'             => (string)$data['practice']->term_days,
            'ToBePrinted'           => 'N',
            'ToBeEmailed'           => 'Y',
            'PT.Billing Report:'    => (string)$link,
            'Line Item'             => 'CPT 99490',
            'LineQty'               => (string)$data['billable'],
            'LineDesc'              => 'CCM Services over 20 minutes',
            'LineUnitPrice'         => (string)$data['practice']->clh_pppm,
            'Msg'                   => '"Thank you for your business. Check Payments: CircleLink Health Shippan Landing Workpoint 290 Harbor Drive, Stamford, CT 06902 ACH Payments: JPMorgan Chase Bank Routing Number (ABA): 02110361 Account Number: 693139136
            Account Name: CircleLink Health Account Address: Shippan Landing Workpoint, 290 Harbor Drive, Stamford, CT 06902 Wire Payments: JPMorgan Chase Bank Routing Number (ABA): 021000021 Account Number: 693139136 Account Name: Circle Link Health
            Account Address: Shippan Landing Workpoint, 290 Harbor Drive, Stamford, CT 06902"',
        ];

    }

}
