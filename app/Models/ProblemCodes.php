<?php

namespace App\Models;

use App\Models\CCD\Problem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProblemCodes extends Model
{
    use SoftDeletes;

    public $fillable = [
        'problem_id',
        'code_system_name',
        'code_system_oid',
        'code',
    ];

    public function problem() {
        return $this->belongsTo(Problem::class, 'problem_id');
    }
}
