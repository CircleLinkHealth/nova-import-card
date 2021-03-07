<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * CircleLinkHealth\SharedModels\Entities\DuplicatePatientResolverLog.
 *
 * @property int                             $id
 * @property int|null                        $enrollee_id
 * @property int                             $user_id_kept
 * @property int|null                        $user_id_deleted
 * @property \Illuminate\Support\Collection  $debug_logs
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null                     $deleted_at
 * @method   static                          \Illuminate\Database\Eloquent\Builder|DuplicatePatientResolverLog newModelQuery()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|DuplicatePatientResolverLog newQuery()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|DuplicatePatientResolverLog query()
 * @mixin \Eloquent
 */
class DuplicatePatientResolverLog extends Model
{
    use HasFactory;

    public $fillable = [
        'user_id_kept',
        'debug_logs',
    ];

    protected $casts = [
        'debug_logs' => 'collection',
    ];
}
