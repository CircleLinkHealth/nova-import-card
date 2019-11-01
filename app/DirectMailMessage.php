<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use App\Models\MedicalRecords\Ccda;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

/**
 * App\DirectMailMessage.
 *
 * @property int                                                                        $id
 * @property string                                                                     $message_id
 * @property string                                                                     $from
 * @property string                                                                     $to
 * @property string                                                                     $subject
 * @property string|null                                                                $body
 * @property int|null                                                                   $num_attachments
 * @property \Illuminate\Support\Carbon|null                                            $created_at
 * @property \Illuminate\Support\Carbon|null                                            $updated_at
 * @property \App\Models\MedicalRecords\Ccda[]|\Illuminate\Database\Eloquent\Collection $ccdas
 * @property \App\Media[]|\Illuminate\Database\Eloquent\Collection                      $media
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DirectMailMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DirectMailMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DirectMailMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DirectMailMessage whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DirectMailMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DirectMailMessage whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DirectMailMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DirectMailMessage whereMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DirectMailMessage whereNumAttachments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DirectMailMessage whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DirectMailMessage whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DirectMailMessage whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int|null $ccdas_count
 * @property int|null $media_count
 */
class DirectMailMessage extends Model implements HasMedia
{
    use HasMediaTrait;

    protected $fillable = [
        //We get this from PhiMail API
        'message_id',
        'from',
        'to',
        'subject',
        //The body of the message
        'body',
        //The number of attachments
        'num_attachments',
    ];

    public function ccdas()
    {
        return $this->hasMany(Ccda::class);
    }
}
