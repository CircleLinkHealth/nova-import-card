<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class QuickBooksRow extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'refNumber'             => $this->refNumber,
            'customer'              => $this->customer,
            'txnDate'               => $this->txnDate,
            'allowOnlineACHPayment' => $this->allowOnlineACHPayment,
            'salesTerm'             => $this->salesTerm,
            'toBePrinted'           => $this->toBePrinted,
            'toBeEmailed'           => $this->toBeEmailed,
            'ptBilling Report'      => $this->ptBillingReport,
            'lineItem'              => $this->lineItem,
            'lineQty'               => $this->lineQty,
            'lineDesc'              => $this->lineDesc,
            'lineUnitPrice'         => $this->lineUnitPrice,
            'msg'                   => $this->msg,
        ];
    }
}
