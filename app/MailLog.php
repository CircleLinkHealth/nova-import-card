<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MailLog extends Model
{

    protected $table = 'cpm_mail_logs';


    protected $fillable = [
        'sender_email',
        'receiver_email',
        'body',
        'subject',
        'type',
        'sender_cpm_id',
        'receiver_cpm_id',
        'note_id'
    ];

    /**
     * @return array
     */
    public function note()
    {
        return $this->belongsTo('App\Note', 'note_id');
    }

}
