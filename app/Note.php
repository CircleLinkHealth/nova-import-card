<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $table = 'notes';

    protected $fillable = [
        'patient_id',
        'author_id',
        'logger_id',        
        'body',
        'isTCM',
        'type',
        'performed_at'
    ];


    public function patient()
    {
        return $this->belongsTo('App\User', 'patient_id', 'id');
    }

    public function mail()
    {
        return $this->hasMany('App\MailLog');
    }

    public function call()
    {
        return $this->hasOne('App\Call');
    }

    public function author()
    {
        return $this->belongsTo('App\User', 'author_id', 'id');
    }

    public function program()
    {
        return $this->belongsTo('App\User', 'author_id', 'id');
    }

}
