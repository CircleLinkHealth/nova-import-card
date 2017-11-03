<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProcessedFiles extends \App\BaseModel
{
    protected $fillable = [
        'path', // the path to the file processed by the worker
    ];
}
