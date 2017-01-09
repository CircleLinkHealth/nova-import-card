<?php namespace App\CLH\CCD\ImportedItems;

use App\CLH\CCD\ItemLogger\AllergyLog;
use Illuminate\Database\Eloquent\Model;

class AllergyImport extends Model {

	protected $guarded = [];

	public function ccdLog()
	{
        return $this->belongsTo(AllergyLog::class);
	}
}
