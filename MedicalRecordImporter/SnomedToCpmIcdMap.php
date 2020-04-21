<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecordImporter;

use CircleLinkHealth\SharedModels\Entities\CpmProblem;

/**
 * CircleLinkHealth\Eligibility\MedicalRecordImporter\SnomedToCpmIcdMap.
 *
 * @property int                                                     $id
 * @property int                                                     $snomed_code
 * @property string                                                  $snomed_name
 * @property string                                                  $icd_10_code
 * @property string                                                  $icd_10_name
 * @property \Carbon\Carbon                                          $created_at
 * @property \Carbon\Carbon                                          $updated_at
 * @property string                                                  $icd_9_code
 * @property string                                                  $icd_9_name
 * @property float                                                   $icd_9_avg_usage
 * @property int                                                     $icd_9_is_nec
 * @property int|null                                                $cpm_problem_id
 * @property \CircleLinkHealth\SharedModels\Entities\CpmProblem|null $cpmProblem
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\MedicalRecordImporter\SnomedToCpmIcdMap
 *     whereCpmProblemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereIcd10Code($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereIcd10Name($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\MedicalRecordImporter\SnomedToCpmIcdMap
 *     whereIcd9AvgUsage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereIcd9Code($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereIcd9IsNec($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereIcd9Name($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereSnomedCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereSnomedName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap query()
 * @property int|null                                                                                    $revision_history_count
 */
class SnomedToCpmIcdMap extends \CircleLinkHealth\Core\Entities\BaseModel
{
    protected $fillable = [
        'snomed_code',
        'snomed_name',
        'icd_10_code',
        'icd_10_name',
        'icd_9_code',
        'icd_9_name',
        'cpm_problem_id',
    ];

    public function cpmProblem()
    {
        return $this->belongsTo(CpmProblem::class);
    }
}
