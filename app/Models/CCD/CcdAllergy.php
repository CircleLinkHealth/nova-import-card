<?php namespace App\Models\CCD;

use App\CLH\CCD\ItemLogger\AllergyLog;
use App\User;
use Illuminate\Database\Eloquent\Model;

class CcdAllergy extends Model
{

    protected $fillable = [
        'ccda_id',
        'vendor_id',
        'patient_id',
        'ccd_allergy_log_id',
        'allergen_name',
    ];

    protected $table = 'ccd_allergies';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ccdLog()
    {
        return $this->belongsTo(AllergyLog::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

}
