<?php

namespace App;

use App\Models\MedicalRecords\Ccda;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

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
        'num_attachments'
    ];
    
    public function ccdas() {
        return $this->hasMany(Ccda::class);
    }
}
