<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Message
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
 * @property-read \App\User $recipient
 * @property-read \App\User $sender
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereNoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereReceiverCpmId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereReceiverEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereSeenOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereSenderCpmId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereSenderEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Message extends \App\BaseModel
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
