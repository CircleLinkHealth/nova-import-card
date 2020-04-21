<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * CircleLinkHealth\SharedModels\Entities\ProblemCode.
 *
 * @property int                                                       $id
 * @property int                                                       $problem_id
 * @property string                                                    $code_system_name
 * @property string|null                                               $code_system_oid
 * @property string                                                    $code
 * @property string|null                                               $name
 * @property \Carbon\Carbon|null                                       $created_at
 * @property \Carbon\Carbon|null                                       $updated_at
 * @property string|null                                               $deleted_at
 * @property \CircleLinkHealth\SharedModels\Entities\Problem           $problem
 * @property \CircleLinkHealth\SharedModels\Entities\ProblemCodeSystem $system
 * @method static                                                    bool|null forceDelete()
 * @method static                                                    \Illuminate\Database\Query\Builder|\App\Models\ProblemCode onlyTrashed()
 * @method static                                                    bool|null restore()
 * @method static                                                    \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode whereCode($value)
 * @method static                                                    \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode whereCodeSystemName($value)
 * @method static                                                    \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode whereCodeSystemOid($value)
 * @method static                                                    \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode whereCreatedAt($value)
 * @method static                                                    \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode whereDeletedAt($value)
 * @method static                                                    \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode whereId($value)
 * @method static                                                    \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode whereName($value)
 * @method static                                                    \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode whereProblemId($value)
 * @method static                                                    \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode whereUpdatedAt($value)
 * @method static                                                    \Illuminate\Database\Query\Builder|\App\Models\ProblemCode withTrashed()
 * @method static                                                    \Illuminate\Database\Query\Builder|\App\Models\ProblemCode withoutTrashed()
 * @mixin \Eloquent
 * @property int|null                                                                                    $problem_code_system_id
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode newModelQuery()
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode newQuery()
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode query()
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode whereProblemCodeSystemId($value)
 * @property int|null                                                                                    $revision_history_count
 */
class ProblemCode extends \CircleLinkHealth\Core\Entities\BaseModel
{
    use SoftDeletes;
    const ICD10_CODE = '2.16.840.1.113883.6.3';

    const ICD9_CODE   = '2.16.840.1.113883.6.103';
    const SNOMED_CODE = '2.16.840.1.113883.6.96';

    public $fillable = [
        'problem_code_system_id',
        'problem_id',
        'code_system_name',
        'code_system_oid',
        'code',
    ];

    public function isIcd10()
    {
        return '2.16.840.1.113883.6.3'                                                                     == $this->code_system_oid
            || Str::contains(strtolower($this->code_system_name), ['10']) || $this->problem_code_system_id == \App\Constants::CODE_SYSTEM_NAME_ID_MAP[\App\Constants::ICD10_NAME];
    }

    public function isIcd9()
    {
        return '2.16.840.1.113883.6.103'                                                                     == $this->code_system_oid
               || Str::contains(strtolower($this->code_system_name), ['9']) || $this->problem_code_system_id == \App\Constants::CODE_SYSTEM_NAME_ID_MAP[\App\Constants::ICD9_NAME];
    }

    public function isSnomed()
    {
        return '2.16.840.1.113883.6.96'                                                                           == $this->code_system_oid
               || Str::contains(strtolower($this->code_system_name), ['snomed']) || $this->problem_code_system_id == \App\Constants::CODE_SYSTEM_NAME_ID_MAP[\App\Constants::SNOMED_NAME];
    }

    public function problem()
    {
        return $this->belongsTo(Problem::class, 'problem_id');
    }

    public function resolve()
    {
        if ($this->isSnomed()) {
            $this->code_system_oid  = self::SNOMED_CODE;
            $this->code_system_name = \App\Constants::SNOMED_NAME;
        } elseif ($this->isIcd9()) {
            $this->code_system_oid  = self::ICD9_CODE;
            $this->code_system_name = \App\Constants::ICD9_NAME;
        } elseif ($this->isIcd10()) {
            $this->code_system_oid  = self::ICD10_CODE;
            $this->code_system_name = \App\Constants::ICD10_NAME;
        }

        return $this;
    }

    public function system()
    {
        return $this->belongsTo(ProblemCodeSystem::class, 'problem_code_system_id');
    }
}
