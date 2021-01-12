<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Http\Requests;

class StoreNurseInvoiceApproval extends StoreNurseInvoiceDispute
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'invoiceId' => $this->invoiceIdRules(),
        ];
    }
}
