<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\PatientData\RockyMountain;

/**
 * App\Models\PatientData\RockyMountain\RockyName.
 *
 * @mixin \Eloquent
 *
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\RockyMountain\RockyName newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\RockyMountain\RockyName newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\RockyMountain\RockyName query()
 *
 * @property int|null $revision_history_count
 */
class RockyName extends \CircleLinkHealth\Core\Entities\BaseModel
{
    protected $guarded = [];
}
