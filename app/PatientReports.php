<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class PatientReports extends Model {

	use SoftDeletes;

	//report types
	const CAREPLAN = 'careplan';
    const NOTE = 'note';

	protected $guarded = [];

}
