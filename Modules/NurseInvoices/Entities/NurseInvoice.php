<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Entities;

use Illuminate\Database\Eloquent\Model;

class NurseInvoice extends Model
{
    protected $casts = [
        'month_year'   => 'date',
        'invoice_data' => ' array',
    ];

    protected $fillable = [
        'nurse_info_id',
        'month_year',
        'sent_to_accountant',
        'invoice_data',
    ];

    public function disputes()
    {
        return $this->morphTo(Dispute::class, 'disputable');
    }
}
