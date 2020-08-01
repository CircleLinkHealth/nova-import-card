<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\PracticePull;

use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * App\Models\PracticePull\Medication.
 *
 * @property string                                                                                      $mrn
 * @property string|null                                                                                 $name
 * @property string|null                                                                                 $sig
 * @property \Illuminate\Support\Carbon|null                                                             $start
 * @property \Illuminate\Support\Carbon|null                                                             $stop
 * @property string|null                                                                                 $status
 * @property int                                                                                         $id
 * @property int|null                                                                                    $location_id
 * @property int|null                                                                                    $billing_provider_user_id
 * @property int                                                                                         $practice_id
 * @property \Illuminate\Support\Carbon|null                                                             $created_at
 * @property \Illuminate\Support\Carbon|null                                                             $updated_at
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property int|null                                                                                    $revision_history_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PracticePull\Medication newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PracticePull\Medication newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PracticePull\Medication query()
 * @mixin \Eloquent
 */
class Medication extends BaseModel
{
    protected $dates = [
        'start', 'stop',
    ];
    protected $fillable = [
        'billing_provider_user_id',
        'location_id',
        'practice_id',
        'mrn',
        'name',
        'sig',
        'start',
        'stop',
        'status',
    ];
    protected $table = 'practice_pull_medications';
}
