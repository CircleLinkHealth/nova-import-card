<?php namespace App\CLH\CCD\Vendor;

use App\CLH\CCD\ImportRoutine\CcdImportRoutine;
use Illuminate\Database\Eloquent\Model;

class CcdVendor extends Model {

    protected $guarded = [];

    public function routine()
    {
        return $this->belongsTo(CcdImportRoutine::class, 'ccd_import_routine_id', 'id');
    }
}
