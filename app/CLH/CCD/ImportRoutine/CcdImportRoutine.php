<?php namespace App\CLH\CCD\ImportRoutine;

use App\CLH\CCD\CcdVendor;
use Illuminate\Database\Eloquent\Model;

class CcdImportRoutine extends Model {

	protected $guarded = [];

	public function strategies()
	{
		return $this->hasMany(CcdImportStrategies::class, 'ccd_import_routine_id', 'id');
	}

	public function vendors()
	{
		return $this->hasMany(CcdVendor::class, 'ccd_import_routine_id', 'id');
	}

}
