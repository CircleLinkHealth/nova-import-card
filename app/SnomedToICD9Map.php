<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SnomedToICD9Map extends \App\BaseModel
{
    public $timestamps = false;
    protected $table = 'snomed_to_icd9_map';
}
