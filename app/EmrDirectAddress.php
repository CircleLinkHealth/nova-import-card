<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmrDirectAddress extends \App\BaseModel
{
    public $fillable = [
        'emrDirectable_type',
        'emrDirectable_id',
        'address',
    ];

    /**
     * Get all of the owning contactCardable models.
     */
    public function emrDirectable()
    {
        return $this->morphTo();
    }
}
