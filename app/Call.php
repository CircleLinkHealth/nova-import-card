<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Call extends Model
{

    protected $table = 'calls';
    
    public function note()
    {
        return $this->belongsTo('App\Note', 'note_id', 'id');
    }


}
