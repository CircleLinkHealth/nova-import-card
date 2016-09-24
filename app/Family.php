<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Family extends Model
{

    protected $fillable = ['*'];

    protected $table = 'families';

    public function patients(){

        return $this->hasMany(PatientInfo::class);

    }


}
