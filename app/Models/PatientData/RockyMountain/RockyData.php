<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\PatientData\RockyMountain;

/**
 * App\Models\SupplementalPatientData\RockyMountain\RockyData.
 *
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SupplementalPatientData\RockyMountain\RockyData newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SupplementalPatientData\RockyMountain\RockyData newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SupplementalPatientData\RockyMountain\RockyData query()
 * @property int|null $revision_history_count
 */
class RockyData extends \CircleLinkHealth\Core\Entities\BaseModel
{
    protected $guarded = [];
}
