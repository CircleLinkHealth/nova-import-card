<?php

namespace App\Models;

use App\Nurse;
use Illuminate\Database\Eloquent\Model;

class Holiday extends \App\BaseModel
{
    protected $dates = [
        'date',
    ];

    protected $fillable = [
        'date',
        'nurse_info_id',
    ];

    public function nurse()
    {
        return $this->belongsTo(Nurse::class, 'nurse_info_id', 'id');
    }
}
