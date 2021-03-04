<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

/**
 * App\SnomedToICD9Map.
 *
 * @property int      $id
 * @property int      $ccm_eligible
 * @property string   $code
 * @property string   $name
 * @property float    $avg_usage
 * @property int      $is_nec
 * @property int      $snomed_code
 * @property string   $snomed_name
 * @property int|null $cpm_problem_id
 * @method   static   \Illuminate\Database\Eloquent\Builder|\App\SnomedToICD9Map whereAvgUsage($value)
 * @method   static   \Illuminate\Database\Eloquent\Builder|\App\SnomedToICD9Map whereCcmEligible($value)
 * @method   static   \Illuminate\Database\Eloquent\Builder|\App\SnomedToICD9Map whereCode($value)
 * @method   static   \Illuminate\Database\Eloquent\Builder|\App\SnomedToICD9Map whereCpmProblemId($value)
 * @method   static   \Illuminate\Database\Eloquent\Builder|\App\SnomedToICD9Map whereId($value)
 * @method   static   \Illuminate\Database\Eloquent\Builder|\App\SnomedToICD9Map whereIsNec($value)
 * @method   static   \Illuminate\Database\Eloquent\Builder|\App\SnomedToICD9Map whereName($value)
 * @method   static   \Illuminate\Database\Eloquent\Builder|\App\SnomedToICD9Map whereSnomedCode($value)
 * @method   static   \Illuminate\Database\Eloquent\Builder|\App\SnomedToICD9Map whereSnomedName($value)
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\SnomedToICD9Map newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\SnomedToICD9Map newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\SnomedToICD9Map query()
 * @property int|null                                                                                    $revision_history_count
 */
class SnomedToICD9Map extends \CircleLinkHealth\Core\Entities\BaseModel
{
    public $timestamps = false;
    protected $table   = 'snomed_to_icd9_map';
}
