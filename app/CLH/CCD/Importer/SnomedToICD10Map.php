<?php namespace App\CLH\CCD\Importer;

use Illuminate\Database\Eloquent\Model;

/**
 * App\CLH\CCD\Importer\SnomedToICD10Map
 *
 * @property int $snomed_code
 * @property string $snomed_name
 * @property string $icd_10_code
 * @property string $icd_10_name
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToICD10Map whereIcd10Code($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToICD10Map whereIcd10Name($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToICD10Map whereSnomedCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToICD10Map whereSnomedName($value)
 * @mixin \Eloquent
 */
class SnomedToICD10Map extends \App\BaseModel
{
    public $timestamps = false;
    protected $table = 'snomed_to_icd10_map';
}
