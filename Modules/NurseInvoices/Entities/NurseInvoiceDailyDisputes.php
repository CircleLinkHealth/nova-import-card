<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Entities;

use CircleLinkHealth\Core\Entities\BaseModel;

class NurseInvoiceDailyDispute extends BaseModel
{
    protected $casts = [
        'status'       => 'boolean',
        'disputed_day' => 'date',
    ];

    protected $fillable = [
        'invoice_id',
        'suggested_formatted_time',
        'disputed_formatted_time',
        'disputed_day',
        'status',
        'invalidated',
    ];

    protected $table = 'nurse_invoice_daily_disputes';

//    public function nurseInvoice()
//    {
//        return $this->belongsTo(NurseInvoice::class, 'invoice_id', 'id');
//    }
}
