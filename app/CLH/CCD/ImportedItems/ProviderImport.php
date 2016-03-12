<?php namespace App\CLH\CCD\ImportedItems;

use App\CLH\CCD\ItemLogger\CcdProviderLog;
use Illuminate\Database\Eloquent\Model;

class ProviderImport extends Model {

    protected $guarded = [];

    public function ccdLog()
    {
        return $this->belongsTo(CcdProviderLog::class);
    }

}
