<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Modules\Nurseinvoices;

use Illuminate\Support\Collection;

class NurseInvoiceCsvGenerator
{
    /**
     * @var Collection
     */
    private $invoice;

    public function __construct(Collection $invoice)
    {
        $this->invoice = $invoice;
    }

    public function toCsvArray()
    {
    }
}
