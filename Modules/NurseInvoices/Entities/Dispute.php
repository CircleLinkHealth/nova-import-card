<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Entities;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    protected $fillable = [
        'user_id',
        'reason',
        'resolved_at',
        'resolved_by',
        'resolution_note',
    ];

    public function disputable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
