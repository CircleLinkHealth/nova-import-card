<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    protected $fillable = [
            'invoice_id',
            'user_id',
            'reason',
            'resolved_at',
            'resolution_note',
        ];

    public function disputable()
    {
        return $this->morphTo();
    }
}
