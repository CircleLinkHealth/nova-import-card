<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Customer\Entities\Nurse;

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
}
