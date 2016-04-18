<?php namespace App\App\Ccd;

use App\CLH\CCD\ItemLogger\CcdAllergyLog;
use Illuminate\Database\Eloquent\Model;

class CcdAllergy extends Model {

    protected $guarded = [];
    
    protected $table = 'ccd_allergies';

    public function ccdLog()
    {
        return $this->belongsTo(CcdAllergyLog::class);
    }

}
