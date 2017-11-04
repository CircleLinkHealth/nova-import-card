<?php namespace App\CLH\CCD\Importer;

use App\Models\CPM\CpmProblem;
use Illuminate\Database\Eloquent\Model;

/**
 * App\CLH\CCD\Importer\SnomedToCpmIcdMap
 *
 * @property int $id
 * @property int $snomed_code
 * @property string $snomed_name
 * @property string $icd_10_code
 * @property string $icd_10_name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $icd_9_code
 * @property string $icd_9_name
 * @property float $icd_9_avg_usage
 * @property int $icd_9_is_nec
 * @property int|null $cpm_problem_id
 * @property-read \App\Models\CPM\CpmProblem|null $cpmProblem
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereCpmProblemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereIcd10Code($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereIcd10Name($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereIcd9AvgUsage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereIcd9Code($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereIcd9IsNec($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereIcd9Name($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereSnomedCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereSnomedName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SnomedToCpmIcdMap extends \App\BaseModel
{
    protected $guarded = [];

    public function cpmProblem()
    {
        return $this->belongsTo(CpmProblem::class);
    }
}
