<?php

namespace App;

use App\Models\Ehr;

class TargetPatient extends BaseModel
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function enrollee()
    {
        return $this->belongsTo(Enrollee::class, 'enrollee_id');
    }

    public function ehr()
    {
        return $this->belongsTo(Ehr::class, 'ehr_id');
    }
}
