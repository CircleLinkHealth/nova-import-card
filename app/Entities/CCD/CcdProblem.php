<?php namespace App\Entities\CCD;

use App\CLH\CCD\ItemLogger\CcdProblemLog;
use App\User;
use Illuminate\Database\Eloquent\Model;

class CcdProblem extends Model {

    protected $guarded = [];

    protected $table = 'ccd_problems';

    public function ccdLog()
    {
        return $this->belongsTo(CcdProblemLog::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function patients()
    {
        return $this->belongsToMany(User::class, 'ccd_problems_patients', 'ccd_problem_id', 'patient_id');
    }
}
