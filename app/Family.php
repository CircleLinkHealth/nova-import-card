<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Family extends Model
{

    protected $fillable = ['*'];

    protected $table = 'families';

    public function patients(){

        $this->hasMany(PatientInfo::class, 'id', 'user_id');

    }


}
