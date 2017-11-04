<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\MailLog
 *
 * @property int $id
 * @property string|null $sender_email
 * @property string|null $receiver_email
 * @property string $body
 * @property string $subject
 * @property string $type
 * @property int $sender_cpm_id
 * @property int $receiver_cpm_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property int|null $note_id
 * @property string|null $seen_on
 * @property-read \App\Note|null $note
 * @property-read \App\User $receiverUser
 * @property-read \App\User $senderUser
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MailLog whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MailLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MailLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MailLog whereNoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MailLog whereReceiverCpmId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MailLog whereReceiverEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MailLog whereSeenOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MailLog whereSenderCpmId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MailLog whereSenderEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MailLog whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MailLog whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\MailLog whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MailLog extends \App\BaseModel
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

    public function senderUser()
    {
        return $this->belongsTo(User::class, 'sender_cpm_id');
    }

    public function receiverUser()
    {
        return $this->belongsTo(User::class, 'receiver_cpm_id');
    }

    public function note()
    {
        return $this->belongsTo('App\Note', 'note_id');
    }
}
