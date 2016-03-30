<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class PatientReports extends Model {

	use SoftDeletes;

	protected $fillable = ['patient_id','patient_mrn','provider_id', 'file_path','file_type'];

}
