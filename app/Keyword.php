<?php

namespace App;

use App\Models\CPM\CpmProblem;
use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    protected $fillable = [
        'name',
        'cpm_problem_id',
        ];

    public function cpmProblems(){

        return $this->belongsTo(CpmProblem::class);

    }
}
