<?php

namespace CircleLinkHealth\Eligibility\Entities;

use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Database\Eloquent\Model;

class PcmProblem extends Model
{
    protected $fillable = [
        'practice_id',
        'code_type',
        'code',
        'description',
    ];
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function practice() {
        return $this->belongsTo(Practice::class);
    }
}
