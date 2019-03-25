<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Model;
use Storage;

class EhrReportWriterInfo extends Model
{
    protected $fillable = [
        'user_id',
        'google_drive_folder_path',
    ];

    protected $table = 'ehr_report_writer_info';

    public function getFolderUrl()
    {
        return Storage::drive('google')->url($this->google_drive_folder_path);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
