<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ehr extends Model
{
    public $fillable = [
        'name',
        'pdf_dispatcher',
    ];
}
