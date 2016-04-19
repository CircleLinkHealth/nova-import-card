<?php namespace App\Entities\CCD;

use App\CLH\CCD\ItemLogger\CcdAllergyLog;
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
    
    

}
