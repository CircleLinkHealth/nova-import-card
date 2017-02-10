<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContactCard extends Model
{
    public $fillable = [
        'contactcardable_type',
        'contactcardable_id',
        'email',
        'emr_direct',
        'work_phone',
        'cell_phone',
        'home_phone',
    ];

    /**
     * Get all of the owning contactCardable models.
     */
    public function contactCardable()
    {
        return $this->morphTo();
    }
}
