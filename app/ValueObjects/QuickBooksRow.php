<?php
/**
 * Created by PhpStorm.
 * User: kakoushias
 * Date: 14/01/2018
 * Time: 8:04 PM
 */

namespace App\ValueObjects;

/**
 * This value object refers to the row created by the makeRow() method in the PracticeReportsService class,
 * when creating a Quickbook Report.
 */
class QuickBooksRow
{
    protected $refNumber;
    protected $customer;
    protected $txnDate;
    protected $allowOnlineACHPayment;
    protected $salesTerm;
    protected $toBePrinted;
    protected $toBeEmailed;
    protected $ptBillingReport;
    protected $lineItem;
    protected $lineQty;
    protected $lineDesc;
    protected $lineUnitPrice;
    protected $msg;


    /**
     * QuickBooksRow constructor.
     *
     * @param array $array
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


    /**
     * @return mixed
     */
    public function getRefNumber()
    {
        return $this->refNumber;
    }

    /**
     * @param mixed $refNumber
     */
    public function setRefNumber($refNumber)
    {
        $this->refNumber = $refNumber;
    }

    /**
     * @return mixed
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param mixed $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return mixed
     */
    public function getTxnDate()
    {
        return $this->txnDate;
    }

    /**
     * @param mixed $txnDate
     */
    public function setTxnDate($txnDate)
    {
        $this->txnDate = $txnDate;
    }

    /**
     * @return mixed
     */
    public function getAllowOnlineACHPayment()
    {
        return $this->allowOnlineACHPayment;
    }

    /**
     * @param mixed $allowOnlineACHPayment
     */
    public function setAllowOnlineACHPayment($allowOnlineACHPayment)
    {
        $this->allowOnlineACHPayment = $allowOnlineACHPayment;
    }

    /**
     * @return mixed
     */
    public function getSalesTerm()
    {
        return $this->salesTerm;
    }

    /**
     * @param mixed $salesTerm
     */
    public function setSalesTerm($salesTerm)
    {
        $this->salesTerm = $salesTerm;
    }

    /**
     * @return mixed
     */
    public function getToBePrinted()
    {
        return $this->toBePrinted;
    }

    /**
     * @param mixed $toBePrinted
     */
    public function setToBePrinted($toBePrinted)
    {
        $this->toBePrinted = $toBePrinted;
    }

    /**
     * @return mixed
     */
    public function getToBeEmailed()
    {
        return $this->toBeEmailed;
    }

    /**
     * @param mixed $toBeEmailed
     */
    public function setToBeEmailed($toBeEmailed)
    {
        $this->toBeEmailed = $toBeEmailed;
    }

    /**
     * @return mixed
     */
    public function getPtBillingReport()
    {
        return $this->ptBillingReport;
    }

    /**
     * @param mixed $ptBillingReport
     */
    public function setPtBillingReport($ptBillingReport)
    {
        $this->ptBillingReport = $ptBillingReport;
    }

    /**
     * @return mixed
     */
    public function getLineItem()
    {
        return $this->lineItem;
    }

    /**
     * @param mixed $lineItem
     */
    public function setLineItem($lineItem)
    {
        $this->lineItem = $lineItem;
    }

    /**
     * @return mixed
     */
    public function getLineQty()
    {
        return $this->lineQty;
    }

    /**
     * @param mixed $lineQty
     */
    public function setLineQty($lineQty)
    {
        $this->lineQty = $lineQty;
    }

    /**
     * @return mixed
     */
    public function getLineDesc()
    {
        return $this->lineDesc;
    }

    /**
     * @param mixed $lineDesc
     */
    public function setLineDesc($lineDesc)
    {
        $this->lineDesc = $lineDesc;
    }

    /**
     * @return mixed
     */
    public function getLineUnitPrice()
    {
        return $this->lineUnitPrice;
    }

    /**
     * @param mixed $lineUnitPrice
     */
    public function setLineUnitPrice($lineUnitPrice)
    {
        $this->lineUnitPrice = $lineUnitPrice;
    }

    /**
     * @return mixed
     */
    public function getMsg()
    {
        return $this->msg;
    }

    /**
     * @param mixed $msg
     */
    public function setMsg($msg)
    {
        $this->msg = $msg;
    }
}
