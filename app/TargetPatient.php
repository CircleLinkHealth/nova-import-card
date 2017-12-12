<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TargetPatient extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function updateOrCreate()
    {
        //
    }


}
