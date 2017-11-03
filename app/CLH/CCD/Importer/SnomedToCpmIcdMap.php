<?php namespace App\CLH\CCD\Importer;

use App\Models\CPM\CpmProblem;
use Illuminate\Database\Eloquent\Model;

class SnomedToCpmIcdMap extends \App\BaseModel
{
    protected $guarded = [];

    public function cpmProblem()
    {
        return $this->belongsTo(CpmProblem::class);
    }
}
