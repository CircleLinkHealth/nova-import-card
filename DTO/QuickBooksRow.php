<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\DTO;

/**
 * This value object refers to the row created by the makeRow() method in the PracticeReportsService class,
 * when creating a Quickbook Report.
 */
class QuickBooksRow
{
    protected $allowOnlineACHPayment;
    protected $customer;
    protected $lineDesc;
    protected $lineItem;
    protected $lineQty;
    protected $lineUnitPrice;
    protected $msg;
    protected $ptBillingReport;
    protected $refNumber;
    protected $salesTerm;
    protected $toBeEmailed;
    protected $toBePrinted;
    protected $txnDate;

    /**
     * QuickBooksRow constructor.
     */
    public function __construct(array $array)
    {
        $this->refNumber             = $array['RefNumber'];
        $this->customer              = $array['Customer'];
        $this->txnDate               = $array['TxnDate'];
        $this->allowOnlineACHPayment = $array['AllowOnlineACHPayment'];
        $this->salesTerm             = $array['SalesTerm'];
        $this->toBePrinted           = $array['ToBePrinted'];
        $this->toBeEmailed           = $array['ToBeEmailed'];
        $this->ptBillingReport       = $array['Pt. billing report:'];
        $this->lineItem              = $array['Line Item'];
        $this->lineQty               = $array['LineQty'];
        $this->lineDesc              = $array['LineDesc'];
        $this->lineUnitPrice         = $array['LineUnitPrice'];
        $this->msg                   = $array['Msg'];
    }

    /**
     * @return mixed
     */
    public function getAllowOnlineACHPayment()
    {
        return $this->allowOnlineACHPayment;
    }

    /**
     * @return mixed
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @return mixed
     */
    public function getLineDesc()
    {
        return $this->lineDesc;
    }

    /**
     * @return mixed
     */
    public function getLineItem()
    {
        return $this->lineItem;
    }

    /**
     * @return mixed
     */
    public function getLineQty()
    {
        return $this->lineQty;
    }

    /**
     * @return mixed
     */
    public function getLineUnitPrice()
    {
        return $this->lineUnitPrice;
    }

    /**
     * @return mixed
     */
    public function getMsg()
    {
        return $this->msg;
    }

    /**
     * @return mixed
     */
    public function getPtBillingReport()
    {
        return $this->ptBillingReport;
    }

    /**
     * @return mixed
     */
    public function getRefNumber()
    {
        return $this->refNumber;
    }

    /**
     * @return mixed
     */
    public function getSalesTerm()
    {
        return $this->salesTerm;
    }

    /**
     * @return mixed
     */
    public function getToBeEmailed()
    {
        return $this->toBeEmailed;
    }

    /**
     * @return mixed
     */
    public function getToBePrinted()
    {
        return $this->toBePrinted;
    }

    /**
     * @return mixed
     */
    public function getTxnDate()
    {
        return $this->txnDate;
    }

    /**
     * @param mixed $allowOnlineACHPayment
     */
    public function setAllowOnlineACHPayment($allowOnlineACHPayment)
    {
        $this->allowOnlineACHPayment = $allowOnlineACHPayment;
    }

    /**
     * @param mixed $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * @param mixed $lineDesc
     */
    public function setLineDesc($lineDesc)
    {
        $this->lineDesc = $lineDesc;
    }

    /**
     * @param mixed $lineItem
     */
    public function setLineItem($lineItem)
    {
        $this->lineItem = $lineItem;
    }

    /**
     * @param mixed $lineQty
     */
    public function setLineQty($lineQty)
    {
        $this->lineQty = $lineQty;
    }

    /**
     * @param mixed $lineUnitPrice
     */
    public function setLineUnitPrice($lineUnitPrice)
    {
        $this->lineUnitPrice = $lineUnitPrice;
    }

    /**
     * @param mixed $msg
     */
    public function setMsg($msg)
    {
        $this->msg = $msg;
    }

    /**
     * @param mixed $ptBillingReport
     */
    public function setPtBillingReport($ptBillingReport)
    {
        $this->ptBillingReport = $ptBillingReport;
    }

    /**
     * @param mixed $refNumber
     */
    public function setRefNumber($refNumber)
    {
        $this->refNumber = $refNumber;
    }

    /**
     * @param mixed $salesTerm
     */
    public function setSalesTerm($salesTerm)
    {
        $this->salesTerm = $salesTerm;
    }

    /**
     * @param mixed $toBeEmailed
     */
    public function setToBeEmailed($toBeEmailed)
    {
        $this->toBeEmailed = $toBeEmailed;
    }

    /**
     * @param mixed $toBePrinted
     */
    public function setToBePrinted($toBePrinted)
    {
        $this->toBePrinted = $toBePrinted;
    }

    /**
     * @param mixed $txnDate
     */
    public function setTxnDate($txnDate)
    {
        $this->txnDate = $txnDate;
    }

    public function toArray()
    {
        return [
            'RefNumber'             => $this->refNumber,
            'Customer'              => $this->customer,
            'TxnDate'               => $this->txnDate,
            'AllowOnlineACHPayment' => $this->allowOnlineACHPayment,
            'SalesTerm'             => $this->salesTerm,
            'ToBePrinted'           => $this->toBePrinted,
            'ToBeEmailed'           => $this->toBeEmailed,
            'Pt. billing report:'   => $this->ptBillingReport,
            'LineItem'              => $this->lineItem,
            'LineQty'               => $this->lineQty,
            'LineDesc'              => $this->lineDesc,
            'LineUnitPrice'         => $this->lineUnitPrice,
            'Msg'                   => $this->msg,
        ];
    }
}
