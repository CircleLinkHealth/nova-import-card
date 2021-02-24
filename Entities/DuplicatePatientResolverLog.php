<?php

namespace CircleLinkHealth\SharedModels\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DuplicatePatientResolverLog extends Model
{
    use HasFactory;

    public $fillable = [
        'user_id_kept',
        'debug_logs',
    ];
    
    protected $casts = [
        'debug_logs' => 'collection'
    ];
}
