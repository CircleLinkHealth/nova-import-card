<?php namespace App\Models\CCD;

use App\CLH\CCD\ItemLogger\CcdProblemLog;
use App\Models\CPM\CpmProblem;
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
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
