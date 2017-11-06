<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Family extends \App\BaseModel
{

    protected $fillable = ['*'];

    protected $table = 'families';

    public function patients()
    {

        return $this->hasMany(Patient::class);
    }

//    public function getClosestCallDateForFamily(){
//
//        return $this->patients()->users()->inboundCalls()->whereStatus('scheduled')->first();
//
//
//    }
}
