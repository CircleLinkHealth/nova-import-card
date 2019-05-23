<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Customer\Entities\Nurse;
use Illuminate\Database\Eloquent\Model;

class NurseInvoice extends Model
{
    protected $casts = [
        'month_year'   => 'date',
        'invoice_data' => ' array',
    ];

    protected $fillable = ['nurse_info_id', 'month_year', 'sent_to_accountant', 'invoice_data'];
    protected $table    = 'nurse_invoices';

    public function dispute()
    {
        return $this->morphMany(Nurse::class, 'disputable');
    }
}
