<?php namespace App\Entities\CCD;

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
    public function patients()
    {
        return $this->belongsToMany(User::class, 'ccd_allergies_patients', 'ccd_allergy_id', 'patient_id');
    }

}
