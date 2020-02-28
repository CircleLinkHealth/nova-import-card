<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * CircleLinkHealth\SharedModels\Entities\ProblemCodeSystem.
 *
 * @property int                 $id
 * @property string              $name
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null         $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProblemCodeSystem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProblemCodeSystem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProblemCodeSystem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProblemCodeSystem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProblemCodeSystem whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Revisionable\Entities\Revision[] $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProblemCodeSystem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProblemCodeSystem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProblemCodeSystem query()
 * @property int|null $revision_history_count
 */
class ProblemCodeSystem extends BaseModel
{
    const ICD10_NAME = 'ICD-10';
    const ICD9_NAME = 'ICD-9';
    const SNOMED_NAME                = 'SNOMED CT';
    
    const ICD10      = 'icd_10_code';
    const ICD9 = 'icd_9_code';
    const SNOMED                     = 'snomed_code';
    
    public $fillable = ['name'];
}
