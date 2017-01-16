<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SnomedToICD9Map extends Model
{
    public $timestamps = false;
    protected $table = 'snomed_to_icd9_map';
}
