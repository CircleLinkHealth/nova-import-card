<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Core\Entities\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\PatientReports.
 *
 * @property int            $id
 * @property int            $patient_id
 * @property string         $patient_mrn
 * @property string         $provider_id
 * @property string         $file_type
 * @property int            $location_id
 * @property string         $file_base64
 * @property string|null    $deleted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\PatientReports onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientReports whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientReports whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientReports whereFileBase64($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientReports whereFileType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientReports whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientReports whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientReports wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientReports wherePatientMrn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientReports whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientReports whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\PatientReports withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\PatientReports withoutTrashed()
 * @mixin \Eloquent
 *
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientReports newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientReports newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientReports query()
 *
 * @property int|null $revision_history_count
 */
class PatientReports extends BaseModel
{
    use SoftDeletes;

    protected $guarded = [];
}
