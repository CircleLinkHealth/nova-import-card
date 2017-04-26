<?php

namespace App\Models;

use App\Nurse;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $dates = [
        'date',
    ];

    protected $fillable = [
        'date',
    ];

    public function nurse()
    {
        return $this->belongsTo(Nurse::class, 'nurse_info_id', 'id');
    }
}
