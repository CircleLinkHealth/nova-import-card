<?php

namespace CircleLinkHealth\Eligibility\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RpmProblem extends Model
{
    protected $fillable = [
        'practice_id',
        'code_type',
        'code',
        'description',
    ];
}
