<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Entities;

use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\NurseInvoices\Traits\Disputable;
use CircleLinkHealth\NurseInvoices\Traits\Nursable;
use Illuminate\Database\Eloquent\Model;

class NurseInvoice extends Model
{
    use Disputable;
    use Nursable;

    protected $casts = [
        'month_year'   => 'date',
        'invoice_data' => ' array',
    ];

    protected $fillable = [
        'nurse_info_id',
        'month_year',
        'sent_to_accountant_at',
        'invoice_data',
    ];

    public function dispute()
    {
        return $this->morphOne(Dispute::class, 'disputable');
    }

    public function nurse()
    {
        return $this->belongsTo(Nurse::class, 'nurse_info_id');
    }

    public function scopeUndisputed($builder)
    {
        return $builder->doesntHave('dispute');
    }
}
