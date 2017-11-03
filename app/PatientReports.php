<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientReports extends \App\BaseModel
{

    use SoftDeletes;

    protected $guarded = [];
}
