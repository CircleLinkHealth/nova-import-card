<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class NurseMonthlySummary extends Model
{

    protected $fillable = ['*'];

    public function nurse(){

        $this->belongsTo(NurseInfo::class, 'id' ,'nurse_id');

    }


}
