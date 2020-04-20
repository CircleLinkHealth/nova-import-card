<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use CircleLinkHealth\Eligibility\Entities\TargetPatient;

/**
 * CircleLinkHealth\Customer\Entities\Ehr.
 *
 * @property int                                                                                     $id
 * @property string                                                                                  $name
 * @property string                                                                                  $pdf_report_handler
 * @property \Carbon\Carbon|null                                                                     $created_at
 * @property \Carbon\Carbon|null                                                                     $updated_at
 * @property \CircleLinkHealth\Customer\Entities\Practice[]|\Illuminate\Database\Eloquent\Collection $practices
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ehr whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ehr whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ehr whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ehr wherePdfReportHandler($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ehr whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection     $revisionHistory
 * @property \CircleLinkHealth\Eligibility\Entities\TargetPatient[]|\Illuminate\Database\Eloquent\Collection $targetPatient
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Ehr newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Ehr newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Ehr query()
 * @property int|null $practices_count
 * @property int|null $revision_history_count
 * @property int|null $target_patient_count
 */
class Ehr extends \CircleLinkHealth\Core\Entities\BaseModel
{
    public $fillable = [
        'name',
        'pdf_report_handler',
    ];

    public function practices()
    {
        return $this->hasMany(Practice::class);
    }

    public function targetPatient()
    {
        return $this->hasMany(TargetPatient::class);
    }
}
