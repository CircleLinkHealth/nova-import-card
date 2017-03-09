<?php namespace App\Models\CCD;

use App\CLH\CCD\ImportRoutine\CcdImportRoutine;
use Illuminate\Database\Eloquent\Model;

class CcdVendor extends Model {

    use App\Traits\Relationships\MedicalRecordItemLoggerRelationships;

    protected $guarded = [];

    public function routine()
    {
        return $this->belongsTo(CcdImportRoutine::class, 'ccd_import_routine_id', 'id');
    }
}
