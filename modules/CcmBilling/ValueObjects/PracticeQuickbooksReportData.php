<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\ValueObjects;

class PracticeQuickbooksReportData
{
    public string $allowOnlineClhPayment = 'Y';
    public string $customer;
    public int $invoiceNo;
    public string $lineDesc;
    public string $lineItem;
    public string $lineQty;
    public string $lineUnitPrice;
    public string $msg = 'ACH Payments: Silicon Valley Bank
Routing Number (ABA): 121140399
Account Number: 3302397258
Account Name: CIRCLELINK HEALTH INC.
';
    public string $patientBillingReportLink;
    public string $salesTerm;
    public string $toBeEmailed = 'Y';
    public string $toBePrinted = 'N';
    public string $txnDate;

    public function toCsvRow(): array
    {
        return [
            'RefNumber'             => (string) $this->invoiceNo,
            'Customer'              => $this->customer,
            'TxnDate'               => $this->txnDate,
            'AllowOnlineACHPayment' => $this->allowOnlineClhPayment,
            'SalesTerm'             => $this->salesTerm,
            'ToBePrinted'           => $this->toBePrinted,
            'ToBeEmailed'           => $this->toBeEmailed,
            'Pt. billing report:'   => $this->patientBillingReportLink,
            'LineItem'              => $this->lineItem,
            'LineQty'               => $this->lineQty,
            'LineDesc'              => $this->lineDesc,
            'LineUnitPrice'         => $this->lineUnitPrice,
            'Msg'                   => $this->msg,
        ];
    }
}
