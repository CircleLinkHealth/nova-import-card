<?php namespace App\Importer\Models\ImportedItems;

use App\Importer\Models\ItemLogs\AllergyLog;
use Illuminate\Database\Eloquent\Model;

class AllergyImport extends Model {

	protected $guarded = [];

	public function ccdLog()
	{
        return $this->belongsTo(AllergyLog::class);
	}
}
