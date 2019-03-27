<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models;

use App\Models\CCD\Problem;
use App\ProblemCodeSystem;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\ProblemCode.
 *
 * @property int                     $id
 * @property int                     $problem_id
 * @property string                  $code_system_name
 * @property string|null             $code_system_oid
 * @property string                  $code
 * @property string|null             $name
 * @property \Carbon\Carbon|null     $created_at
 * @property \Carbon\Carbon|null     $updated_at
 * @property string|null             $deleted_at
 * @property \App\Models\CCD\Problem $problem
 * @property \App\ProblemCodeSystem  $system
 *
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ProblemCode onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode whereCodeSystemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode whereCodeSystemOid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode whereProblemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ProblemCode withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ProblemCode withoutTrashed()
 * @mixin \Eloquent
 */
class ProblemCode extends \CircleLinkHealth\Core\Entities\BaseModel
{
    use SoftDeletes;

    public $fillable = [
        'problem_code_system_id',
        'problem_id',
        'code_system_name',
        'code_system_oid',
        'code',
    ];
    public $ICD10_CODE = '2.16.840.1.113883.6.3';
    public $ICD9_CODE  = '2.16.840.1.113883.6.103';

    public $SNOMED_CODE = '2.16.840.1.113883.6.96';

    public function isIcd10()
    {
        return '2.16.840.1.113883.6.3' == $this->code_system_oid
            || str_contains(strtolower($this->code_system_name), ['10']);
    }

    public function isIcd9()
    {
        return '2.16.840.1.113883.6.103' == $this->code_system_oid
            || str_contains(strtolower($this->code_system_name), ['9']);
    }

    public function isSnomed()
    {
        return '2.16.840.1.113883.6.96' == $this->code_system_oid
            || str_contains(strtolower($this->code_system_name), ['snomed']);
    }

    public function problem()
    {
        return $this->belongsTo(Problem::class, 'problem_id');
    }

    public function resolve()
    {
        if ($this->isSnomed()) {
            $this->code_system_oid = $this->SNOMED_CODE;
        } elseif ($this->isIcd9()) {
            $this->code_system_oid = $this->ICD9_CODE;
        } elseif ($this->isIcd10()) {
            $this->code_system_oid = $this->ICD10_CODE;
        }

        return $this;
    }

    public function system()
    {
        return $this->belongsTo(ProblemCodeSystem::class, 'problem_code_system_id');
    }
}
