<?php

namespace App\Models;

use App\Practice;
use Illuminate\Database\Eloquent\Model;

class Ehr extends \App\BaseModel
{
    public $fillable = [
        'name',
        'pdf_report_handler',
    ];

    public function practices()
    {
        return $this->hasMany(Practice::class);
    }
}
