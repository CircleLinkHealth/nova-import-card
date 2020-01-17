<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\CLH\CCD\Importer;

/**
 * App\CLH\CCD\Importer\SnomedToICD10Map.
 *
 * @property int    $snomed_code
 * @property string $snomed_name
 * @property string $icd_10_code
 * @property string $icd_10_name
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToICD10Map whereIcd10Code($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToICD10Map whereIcd10Name($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToICD10Map whereSnomedCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToICD10Map whereSnomedName($value)
 * @mixin \Eloquent
 * @property \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Revisionable\Entities\Revision[] $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToICD10Map newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToICD10Map newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToICD10Map query()
 * @property int|null $revision_history_count
 */
class SnomedToICD10Map extends \CircleLinkHealth\Core\Entities\BaseModel
{
    public $timestamps = false;
    protected $table   = 'snomed_to_icd10_map';
}
