<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $table = 'notes';

    protected $fillable = [

    ];


    public function user()
    {
        return $this->belongsTo('App\User', 'patient_id', 'ID');
    }

    public function mail()
    {
        return $this->morphMany('App\MailLog', 'mailable');
    }

    public function call()
    {
        return $this->hasOne('App\Call');
    }

}
