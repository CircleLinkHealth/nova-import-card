<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Entities;

use App\Models\MedicalRecords\Ccda;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * App\Entities\CcdaRequest.
 *
 * @property int                                  $id
 * @property int|null                             $ccda_id
 * @property string                               $vendor
 * @property int                                  $patient_id
 * @property int                                  $department_id
 * @property int                                  $practice_id
 * @property int|null                             $successful_call
 * @property int|null                             $document_id
 * @property \Carbon\Carbon|null                  $created_at
 * @property \Carbon\Carbon|null                  $updated_at
 * @property \App\Models\MedicalRecords\Ccda|null $ccda
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\CcdaRequest whereCcdaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\CcdaRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\CcdaRequest whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\CcdaRequest whereDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\CcdaRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\CcdaRequest wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\CcdaRequest wherePracticeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\CcdaRequest whereSuccessfulCall($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\CcdaRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\CcdaRequest whereVendor($value)
 * @mixin \Eloquent
 *
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\CcdaRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\CcdaRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\CcdaRequest query()
 */
class CcdaRequest extends \CircleLinkHealth\Core\Entities\BaseModel implements Transformable
{
    use TransformableTrait;

    protected $fillable = [
        'ccda_id',
        'vendor',
        'patient_id',
        'department_id',
        'practice_id',
        'successful_call',
    ];

    public function ccda()
    {
        return $this->belongsTo(Ccda::class);
    }
}
