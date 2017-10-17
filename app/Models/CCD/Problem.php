<?php namespace App\Models\CCD;

use App\CLH\CCD\Importer\SnomedToCpmIcdMap;
use App\Importer\Models\ItemLogs\ProblemLog;
use App\Models\CPM\CpmProblem;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Problem extends Model
{

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
        return $this->belongsTo(ProblemLog::class);
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

    public function isSnomed() {
        return $this->code_system == '2.16.840.1.113883.6.96'
            || str_contains(strtolower($this->code_system_name), ['snomed']);
    }

    public function isIcd9() {
        return $this->code_system == '2.16.840.1.113883.6.103'
            || str_contains(strtolower($this->code_system_name), ['9']);
    }

    public function isIcd10() {
        return $this->code_system == '2.16.840.1.113883.6.3'
            || str_contains(strtolower($this->code_system_name), ['10']);
    }

    public function hasIcd10BillingCode() {
        return !empty($this->icd_10_code);
    }

    public function icd10Code() {
        if ($this->hasIcd10BillingCode()) {
            return $this->icd_10_code;
        }

        if ($this->isIcd10() && $this->code) {
            return $this->code;
        }

        return $this->cpmProblem->default_icd_10_code ?? null;
    }

    public function convertCode($from, $to) {
        return SnomedToCpmIcdMap::where($from, '=', $this->code)
            ->whereNotNull($to)
            ->where($to, '!=', '')
            ->first()
            ->{$to} ?? null;
    }
}
