<?php

namespace App\Models;

use App\Models\CCD\Problem;
use App\Scopes\Imported;
use App\Scopes\WithNonImported;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProblemCode extends Model
{
    use SoftDeletes;

    public $fillable = [
        'problem_id',
        'code_system_name',
        'code_system_oid',
        'code',
    ];

    public function problem()
    {
        return $this->belongsTo(Problem::class, 'problem_id');
    }

    public function isSnomed()
    {
        return $this->code_system_oid == '2.16.840.1.113883.6.96'
            || str_contains(strtolower($this->code_system_name), ['snomed']);
    }

    public function isIcd9()
    {
        return $this->code_system_oid == '2.16.840.1.113883.6.103'
            || str_contains(strtolower($this->code_system_name), ['9']);
    }

    public function isIcd10()
    {
        return $this->code_system_oid == '2.16.840.1.113883.6.3'
            || str_contains(strtolower($this->code_system_name), ['10']);
    }
}
