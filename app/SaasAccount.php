<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaasAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'logo_path',
    ];
}
