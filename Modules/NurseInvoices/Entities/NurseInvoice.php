<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Entities;

use App\Traits\NotificationAttachable;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\NurseInvoices\Traits\Disputable;
use CircleLinkHealth\NurseInvoices\Traits\Nursable;
use Illuminate\Database\Eloquent\Model;

class NurseInvoice extends Model
{
    use Disputable;
    use NotificationAttachable;
    use Nursable;

    protected $casts = [
        'month_year'   => 'date',
        'invoice_data' => ' array',
    ];

    protected $dates = [
        'month_year',
        'nurse_approved_at',
        'sent_to_accountant_at',
    ];

    protected $fillable = [
        'nurse_info_id',
        'month_year',
        'sent_to_accountant_at',
        'invoice_data',
        'approval_date',
        'is_nurse_approved',
        'nurse_approved_at',
    ];

    public function dispute()
    {
        return $this->morphOne(Dispute::class, 'disputable');
    }

    public function nurse()
    {
        return $this->belongsTo(Nurse::class, 'nurse_info_id');
    }

    public function scopeApproved($builder)
    {
        return $builder->where('is_nurse_approved', true);
    }

    public function scopeNotApproved($builder)
    {
        return $builder->whereNull('is_nurse_approved');
    }

    public function scopeUndisputed($builder)
    {
        return $builder->doesntHave('dispute');
    }
}
