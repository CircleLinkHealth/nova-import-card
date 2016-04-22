<?php namespace App\Entities\CCD;

use App\CLH\CCD\ItemLogger\CcdProblemLog;
use App\Entities\CPM\CpmProblem;
use App\User;
use Illuminate\Database\Eloquent\Model;

class CcdProblem extends Model {

    protected $guarded = [];

    protected $table = 'ccd_problems';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ccdLog()
    {
        return $this->belongsTo(CcdProblemLog::class);
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
    public function patients()
    {
        return $this->belongsToMany(User::class, 'ccd_problems_patients', 'ccd_problem_id', 'patient_id');
    }
}
