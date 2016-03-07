<?php namespace App\CLH\CCD\Vendor;

use App\CLH\CCD\ItemLogger\ModelLogRelationship;
use App\CLH\CCD\ImportRoutine\CcdImportRoutine;
use Illuminate\Database\Eloquent\Model;

class CcdVendor extends Model {

    use ModelLogRelationship;

    protected $guarded = [];

    public function routine()
    {
        return $this->belongsTo(CcdImportRoutine::class, 'ccd_import_routine_id', 'id');
    }
}
