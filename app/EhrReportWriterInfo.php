<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EhrReportWriterInfo extends Model
{
    protected $fillable = [
        'user_id',
        'google_drive_folder',
    ];

    protected $table = 'ehr_report_writer_info';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
