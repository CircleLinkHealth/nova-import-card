<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Entities;

use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\User;

class NurseInvoiceExtra extends BaseModel
{
    protected $casts = [
        'date' => 'date',
    ];
    protected $fillable = ['user_id', 'date', 'unit', 'value'];
    protected $table    = 'nurse_invoice_extras';

    public function nurse()
    {
        return $this->belongsTo(Nurse::class, 'user_id', 'user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
