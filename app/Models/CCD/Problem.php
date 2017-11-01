<?php namespace App\Models\CCD;

use App\CLH\CCD\Importer\SnomedToCpmIcdMap;
use App\Importer\Models\ItemLogs\ProblemLog;
use App\Models\CPM\CpmProblem;
use App\Models\ProblemCode;
use App\Scopes\Imported;
use App\Scopes\WithNonImported;
use App\Traits\HasProblemCodes;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Problem extends Model implements \App\Contracts\Models\CCD\Problem
{
    use HasProblemCodes;

    protected $fillable = [
        'ccda_id',
        'vendor_id',
        'ccd_problem_log_id',
        'name',
        'icd_10_code',
        'code',
        'code_system',
        'code_system_name',
        'activate',
        'cpm_problem_id',
        'patient_id',
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

    public function convertCode($from, $to)
    {
        return SnomedToCpmIcdMap::where($from, '=', $this->code)
            ->whereNotNull($to)
            ->where($to, '!=', '')
            ->first()
            ->{$to} ?? null;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function codes()
    {
        return $this->hasMany(ProblemCode::class);
    }
}
