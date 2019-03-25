<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use CircleLinkHealth\Customer\Entities\Practice;
use App\TargetPatient;

/**
 * CircleLinkHealth\Customer\Entities\Ehr.
 *
 * @property int                                                      $id
 * @property string                                                   $name
 * @property string                                                   $pdf_report_handler
 * @property \Carbon\Carbon|null                                      $created_at
 * @property \Carbon\Carbon|null                                      $updated_at
 * @property \CircleLinkHealth\Customer\Entities\Practice[]|\Illuminate\Database\Eloquent\Collection $practices
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ehr whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ehr whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ehr whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ehr wherePdfReportHandler($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ehr whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Ehr extends \App\BaseModel
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
