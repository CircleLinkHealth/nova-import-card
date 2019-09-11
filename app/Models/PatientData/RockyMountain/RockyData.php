<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\PatientData\RockyMountain;

/**
 * App\Models\PatientData\RockyMountain\RockyData.
 *
 * @mixin \Eloquent
 *
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\RockyMountain\RockyData newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\RockyMountain\RockyData newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\RockyMountain\RockyData query()
 *
 * @property int|null $revision_history_count
 */
class RockyData extends \CircleLinkHealth\Core\Entities\BaseModel
{
    protected $guarded = [];
}
