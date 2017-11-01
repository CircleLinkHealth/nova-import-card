<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{

    protected $fillable = ['*'];

    protected $table = 'cpm_mail_logs';

    public function recipient()
    {

        return $this->belongsTo(User::class, 'receiver_cpm_id', 'id');
    }

    public function sender()
    {

        return $this->belongsTo(User::class, 'sender_cpm_id', 'id');
    }
}
