<?php namespace App\Models\CCD;

use App\CLH\CCD\Importer\SnomedToCpmIcdMap;
use App\Importer\Models\ItemLogs\ProblemLog;
use App\Models\CPM\CpmProblem;
use App\Models\CPM\CpmInstruction;
use App\Models\ProblemCode;
use App\Scopes\Imported;
use App\Scopes\WithNonImported;
use App\Traits\HasProblemCodes;
use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CCD\Problem
 *
 * @property int $id
 * @property int|null $problem_import_id
 * @property int|null $ccda_id
 * @property int $patient_id
 * @property int|null $ccd_problem_log_id
 * @property string|null $name
 * @property int|null $cpm_problem_id
 * @property int|null $cpm_instruction_id
 * @property string|null $deleted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Importer\Models\ItemLogs\ProblemLog|null $ccdLog
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProblemCode[] $codes
 * @property-read \App\Models\CPM\CpmProblem|null $cpmProblem
 * @property-read \App\User $patient
 * @property-read \App\Models\CPM\CpmInstruction $cpmInstruction
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereActivate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereCcdProblemLogId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereCcdaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereCodeSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereCodeSystemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereCpmProblemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereIcd10Code($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereProblemImportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereVendorId($value)
 * @mixin \Eloquent
 */
class Problem extends \App\BaseModel implements \App\Contracts\Models\CCD\Problem
{
    use HasProblemCodes;

    protected $fillable = [
        'is_monitored',
        'problem_import_id',
        'ccda_id',
        'patient_id',
        'ccd_problem_log_id',
        'name',
        'billable',
        'cpm_problem_id',
        'cpm_instruction_id',
    ];

    protected $table = 'ccd_problems';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ccdLog()
    {
        return $this->belongsTo(ProblemLog::class, 'ccd_problem_log_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cpmProblem()
    {
        return $this->belongsTo(CpmProblem::class);
    }
    
    public function cpmInstruction()
    {
        return $this->hasOne(CpmInstruction::class, 'id', 'cpm_instruction_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function icd10Code()
    {
        $icd10 = $this->icd10Codes->first();

        if ($icd10) {
            return $icd10->code;
        }

        return $this->cpmProblem->default_icd_10_code ?? null;
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function codes()
    {
        return $this->hasMany(ProblemCode::class);
    }
}
