<?php namespace App\Models\CCD;

use App\Importer\Models\ItemLogs\ProblemLog;
use App\Models\CPM\CpmProblem;
use App\User;
use Illuminate\Database\Eloquent\Model;

class CcdProblem extends Model
{

    protected $fillable = [
        'ccda_id',
        'vendor_id',
        'ccd_problem_log_id',
        'name',
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
}
