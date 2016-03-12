<?php namespace App\CLH\CCD\ImportedItems;

use App\CLH\CCD\ItemLogger\CcdAllergyLog;
use Illuminate\Database\Eloquent\Model;

class AllergyImport extends Model {

	protected $guarded = [];

	public function ccdLog()
	{
		return $this->belongsTo(CcdAllergyLog::class);
	}
}
