<?php namespace App\CLH\CCD\Importer;

use Illuminate\Database\Eloquent\Model;

class SnomedToICD10Map extends Model
{
    public $timestamps = false;
    protected $table = 'snomed_to_icd10_map';
}
