<?php namespace App\Models\CCD;

use App\CLH\CCD\ItemLogger\CcdAllergyLog;
use App\User;
use Illuminate\Database\Eloquent\Model;

class CcdAllergy extends Model {

    protected $guarded = [];
    
    protected $table = 'ccd_allergies';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ccdLog()
    {
        return $this->belongsTo(CcdAllergyLog::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

}
