<?php

namespace App;

use App\Models\Ehr;
use Illuminate\Database\Eloquent\Model;

class TargetPatient extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function enrollee(){
        return $this->belongsTo(Enrollee::class, 'enrollee_id');
    }

    public function ehr(){

        return $this->belongsTo(Ehr::class, 'ehr_id');
    }
}
